<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $superAdmin = $this->createSuperAdmin();

        $manager->persist($superAdmin);

        $manager->flush();
    }

    /**
     * Le rôle de cette méthode est de créer le super admin
     *
     * @return User
     */
    private function createSuperAdmin() : User
    {

        $superAdmin = new User();

        $passwordHashed = $this->hasher->hashPassword($superAdmin, "azerty12345.A");

        $superAdmin 
                    ->setFirstName("Jean")
                    ->setLastName("Dupont")
                    ->setEmail("medecine-du-monde@gmail.com")
                    ->setRoles(["ROLE_SUPER_ADMIN","ROLE_ADMIN","ROLE_USER"])
                    ->setPassword($passwordHashed)
                    ->setVerified(true)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setUpdatedAt(new DateTimeImmutable())
        ;

        return $superAdmin;
    }

}
