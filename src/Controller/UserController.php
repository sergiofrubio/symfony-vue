<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $repo = $this->em->getRepository(User::class);

        $search = trim((string) $request->query->get('search', ''));
        $role = trim((string) $request->query->get('role', ''));
        $isActiveRaw = $request->query->get('is_active', '');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $qb = $repo->createQueryBuilder('u');

        if ($search !== '') {
            $qb->andWhere('u.email LIKE :search OR u.id = :idExact')
               ->setParameter('search', '%'.$search.'%')
               ->setParameter('idExact', is_numeric($search) ? (int)$search : 0);
        }

        if ($role !== '') {
            // Si roles se guardan como JSON (p. ej. ["ROLE_ADMIN"]), buscamos la cadena con comillas
            // Esto evita emparejamientos parciales (p. ej. ROLE_USER matching ROLE_SUPERUSER)
            $qb->andWhere('u.roles LIKE :roleLike')
               ->setParameter('roleLike', '%"'.$role.'"%');
        }

        if ($isActiveRaw !== '' && $isActiveRaw !== null) {
            if (in_array(strtolower($isActiveRaw), ['1', 'true', 'yes'], true)) {
                $qb->andWhere('u.is_active = :ia')->setParameter('ia', true);
            } elseif (in_array(strtolower($isActiveRaw), ['0', 'false', 'no'], true)) {
                $qb->andWhere('u.is_active = :ia')->setParameter('ia', false);
            }
        }

        // total (sin paginar)
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        // orden y paginación
        $qb->orderBy('u.id', 'ASC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);

        $users = $qb->getQuery()->getResult();

        $items = array_map(function (User $u) {
            return [
                'id' => $u->getId(),
                'email' => $u->getEmail(),
                'roles' => $u->getRoles(),
                'is_active' => $u->isActive(),
                'last_login' => $u->getLastLogin() ? $u->getLastLogin()->format(\DateTime::ATOM) : null,
            ];
        }, $users);

        return new JsonResponse([
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'], priority: 10)]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthenticated'], 401);
        }

        $companies = [];
        foreach ($user->getCompanies() as $company) {
            $companies[] = [
                'id' => $company->getId(),
                'name' => $company->getName(),
                'taxId' => $company->getTaxId(),
                'currency' => $company->getCurrency(),
            ];
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'permissions' => $user->getPermissions(),
            'companies' => $companies,
            'defaultCompany' => $user->getDefaultCompany() ? [
                'id' => $user->getDefaultCompany()->getId(),
                'name' => $user->getDefaultCompany()->getName(),
                'currency' => $user->getDefaultCompany()->getCurrency(),
            ] : null,
            'is_active' => $user->isActive(),
            'last_login' => $user->getLastLogin() ? $user->getLastLogin()->format(\DateTime::ATOM) : null,
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $repo = $this->em->getRepository(User::class);
        $u = $repo->find($id);
        if (!$u) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        return new JsonResponse([
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            'roles' => $u->getRoles(),
            'is_active' => $u->isActive(),
            'last_login' => $u->getLastLogin() ? $u->getLastLogin()->format(\DateTime::ATOM) : null,
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true) ?? [];
        $email = isset($data['email']) ? trim((string)$data['email']) : '';
        $password = $data['password'] ?? null;
        $roles = is_array($data['roles']) ? $data['roles'] : [];
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        if ($email === '' || !$password) {
            return new JsonResponse(['error' => 'email and password are required'], 400);
        }

        $u = new User();
        $u->setEmail($email);
        $u->setIsActive($isActive);
        $hashed = $this->passwordHasher->hashPassword($u, $password);
        $u->setPassword($hashed);

        // map roles (by slug or name)
        $roleRepo = $this->em->getRepository(Role::class);
        foreach ($roles as $r) {
            $slug = (string)$r;
            $roleEntity = $roleRepo->findOneBy(['slug' => $slug]);
            if (!$roleEntity) {
                // create a simple role if missing
                $roleEntity = new Role();
                $roleEntity->setSlug($slug);
                $roleEntity->setName($slug);
                $this->em->persist($roleEntity);
            }
            $u->addRole($roleEntity);
        }

        try {
            $this->em->persist($u);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to create user', 'details' => $e->getMessage()], 400);
        }

        return new JsonResponse(['id' => $u->getId()], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $repo = $this->em->getRepository(User::class);
        $u = $repo->find($id);
        if (!$u) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        $data = json_decode((string) $request->getContent(), true) ?? [];
        if (isset($data['email'])) {
            $u->setEmail((string)$data['email']);
        }
        if (array_key_exists('is_active', $data)) {
            $u->setIsActive((bool)$data['is_active']);
        }
        if (!empty($data['password'])) {
            $hashed = $this->passwordHasher->hashPassword($u, (string)$data['password']);
            $u->setPassword($hashed);
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            // clear existing roles (remove by creating a new collection isn't necessary, use removeRole)
            // we'll load Role entities and sync
            $roleRepo = $this->em->getRepository(Role::class);
            // remove all current roles
            foreach ($u->getRoles() as $existingRoleSlug) {
                // find Role entity by slug
                $re = $roleRepo->findOneBy(['slug' => $existingRoleSlug]);
                if ($re) $u->removeRole($re);
            }

            foreach ($data['roles'] as $r) {
                $slug = (string)$r;
                $roleEntity = $roleRepo->findOneBy(['slug' => $slug]);
                if (!$roleEntity) {
                    $roleEntity = new Role();
                    $roleEntity->setSlug($slug);
                    $roleEntity->setName($slug);
                    $this->em->persist($roleEntity);
                }
                $u->addRole($roleEntity);
            }
        }

        try {
            $this->em->persist($u);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to update user', 'details' => $e->getMessage()], 400);
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $repo = $this->em->getRepository(User::class);
        $u = $repo->find($id);
        if (!$u) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        try {
            $this->em->remove($u);
            $this->em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Unable to delete user', 'details' => $e->getMessage()], 400);
        }

        return new JsonResponse(['success' => true]);
    }
}
