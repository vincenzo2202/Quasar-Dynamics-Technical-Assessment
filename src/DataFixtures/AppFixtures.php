<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;   


class AppFixtures extends Fixture
{
 
    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setUsername('admin');

        $user->setEmail('admin@example.com');
 
        $user->setPassword('Arbol1234'); 

        $now = new DateTime();
        $user->setCreatedAt($now);
        $user->setUpdatedAt($now);

        $manager->persist($user);
        $manager->flush();
    }
}
