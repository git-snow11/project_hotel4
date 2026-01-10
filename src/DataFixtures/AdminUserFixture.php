<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserFixture extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin-example@gmail.com');
        $user->setNom('Admin-nom');
        $user->setPrenom('Admin-prenom');
        $user->setTelephone('0000000000');
        $user->setRoles(['ROLE_ADMIN','ROLE_USER']);

        $user->setPassword($this->hasher->hashPassword($user, 'admin123'));

        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,  // Run AFTER AppFixtures
        ];
    }
}