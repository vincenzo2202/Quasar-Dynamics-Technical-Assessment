<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\CategoryNote;
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
        $faker = Factory::create();

        $users = $this->loadUsers($manager, $faker);
        $notes = $this->loadNotes($manager, $faker, $users);
        $categories = $this->loadCategories($manager, $faker);
        $this->loadCategoryNotes($manager, $faker, $categories, $notes);
    }
    // USERS FIXTURES 
    private function loadUsers(ObjectManager $manager, $faker): array
    {
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setUsername($faker->firstName() . ' ' . $faker->lastName());
            $user->setEmail($faker->email());
            $user->setPassword('Arbol1234');

            $now = new DateTime();
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);

            $manager->persist($user);
            $users[] = $user;
        }
        $manager->flush();

        return $users;
    }
    // NOTES FIXTURES
    private function loadNotes(ObjectManager $manager, $faker, array $users): array
    {
        $notes = [];
        for ($i = 1; $i <= 5; $i++) {
            $note = new Notes();
            $note->setUser($users[$faker->numberBetween(0, count($users) - 1)]);
            $note->setTitle($faker->sentence());
            $note->setNote($faker->text(100));

            $now = new DateTime();
            $note->setCreatedAt($now);
            $note->setUpdatedAt($now);

            $manager->persist($note);
            $notes[] = $note;
        }
        $manager->flush();

        return $notes;
    }
    // CATEGORIES FIXTURES
    private function loadCategories(ObjectManager $manager, $faker): array
    {
        $categories = [];
        for ($i = 1; $i <= 3; $i++) {
            $category = new Category();
            $category->setCategory($faker->word());
            $category->setDescription($faker->text(25));

            $now = new DateTime();
            $category->setCreatedAt($now);
            $category->setUpdatedAt($now);

            $manager->persist($category);
            $categories[] = $category;
        }
        $manager->flush();

        return $categories;
    }
    // CATEGORY NOTES FIXTURES
    private function loadCategoryNotes(ObjectManager $manager, $faker, array $categories, array $notes): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $categoryNote = new CategoryNote();

            $category = $categories[$faker->numberBetween(0, count($categories) - 1)];
            $note = $notes[$faker->numberBetween(0, count($notes) - 1)];

            $categoryNote->setCategory($category);
            $categoryNote->setNote($note);

            $manager->persist($categoryNote);
        }
        $manager->flush();
    }
}
