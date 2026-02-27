<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $roleUser = new Role();
        $roleUser->setName('User');
        $roleUser->setSlug('ROLE_USER');

        $roleAdmin = new Role();
        $roleAdmin->setName('Admin');
        $roleAdmin->setSlug('ROLE_ADMIN');

        $manager->persist($roleUser);
        $manager->persist($roleAdmin);

        // Usuario con rol admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setIsActive(true);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'adminpass')
        );
        $admin->addRole($roleAdmin);

        // Usuario con rol user
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setIsActive(true);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'userpass')
        );
        $user->addRole($roleUser);

        $manager->persist($admin);
        $manager->persist($user);

        $manager->flush();
    }
}
