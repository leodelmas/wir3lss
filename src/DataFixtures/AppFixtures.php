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
        $results = ["OK", "FAILED"];
        for ($i = 0; $i < 20; $i++) {
            $log = new Log();
            $log
                ->setIp($faker->ipv4)
                ->setMac($faker->macAddress)
                ->setSented($faker->dateTime())
                ->setResult($results[rand(0, 1)]);
            $manager->persist($log);
        }

        $manager->flush();
    }
}