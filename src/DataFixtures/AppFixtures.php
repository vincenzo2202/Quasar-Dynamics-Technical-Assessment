<?php

namespace App\DataFixtures;

use App\Entity\Notes;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {

        // USER Fixture to introduce random users in DB
        $faker = Factory::create();

        for ($i = 1; $i <= 20; $i++) {
            $user = new User();
            $user->setUsername($faker->firstName() . ' ' . $faker->lastName());
            $user->setEmail($faker->email());
            $user->setPassword('Arbol1234');

            $now = new DateTime();
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);

            $manager->persist($user);
        }

        // NOTES Fixture to introduce random notes in DB
        for ($i = 1; $i <= 20; $i++) {
            $note = new Notes();

            do {
                $userId = $faker->numberBetween(1, 26);
                $user = $manager->getRepository(User::class)->find($userId);
            } while (!$user);

            $note->setUser($user);

            $note->setTitle($faker->sentence());
            $note->setNote($faker->text(255));

            $now = new DateTime();
            $note->setCreatedAt($now);
            $note->setUpdatedAt($now);

            $manager->persist($note);
        }


        $manager->flush();
    }
}
