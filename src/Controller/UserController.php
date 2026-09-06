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
}
