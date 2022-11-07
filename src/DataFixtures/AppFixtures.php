<?php

namespace App\DataFixtures;

use App\Entity\Log;
use DateTime;
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
                ->setSource("[FAKE] " . $faker->ipv4)
                ->setDestination(strtolower($faker->domainName()))
                ->setSented($faker->dateTime(new DateTime('-1 year')))
                ->setUser("[FAKE] " . strtolower($faker->lastName))
                ->setResult($results[rand(0, 2)]);
            $manager->persist($log);
        }

        $manager->flush();
    }
}
