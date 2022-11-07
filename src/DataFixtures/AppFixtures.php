<?php

namespace App\DataFixtures;

use App\Entity\Log;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();
        $results = ["CONNECT", "CONNECT REDIRECT", "GET REDIRECT"];
        for ($i = 0; $i < 20; $i++) {
            $log = new Log();
            $log
                ->setSource($faker->ipv4)
                ->setDestination($faker->ipv4)
                ->setSented($faker->dateTimeAD())
                ->setUser(strtolower($faker->lastName))
                ->setResult($results[rand(0, 2)]);
            $manager->persist($log);
        }

        $manager->flush();
    }
}
