<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('test');
        $hashedPassword = $this->hasher->hashPassword($user, 'test');
        $user->setPassword($hashedPassword);
        $user->setEmail('test@test.com');
        
        $manager->persist($user);

        $useradmin = new User();
        $useradmin->setUsername('admin');
        $hashedPassword = $this->hasher->hashPassword($useradmin, 'test');
        $useradmin->setPassword($hashedPassword);
        $useradmin->setRoles(['ROLE_ADMIN']);
        $useradmin->setEmail('test@test.com');
        
        $manager->persist($useradmin);

        $manager->flush();
    }
}
