<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthenticated'], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'is_active' => $user->isActive(),
            'last_login' => $user->getLastLogin() ? $user->getLastLogin()->format(\DateTime::ATOM) : null,
        ]);
    }
}
