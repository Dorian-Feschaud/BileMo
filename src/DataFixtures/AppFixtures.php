<?php

namespace App\DataFixtures;

use App\Factory\CustomerFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct()
    {
    }

    public function load(ObjectManager $manager):void
    {
        UserFactory::createOne(
            static function() {
                return [
                    'firstname' => "Super",
                    'lastname' => "Admin",
                    'email' => "superadmin@example.com",
                    'roles' => ["ROLE_SUPER_ADMIN"]
                ];
            }
        );

        UserFactory::createMany(
            5,
            static function() {
                return ['roles' => ["ROLE_ADMIN"]];
            }
        );

        UserFactory::createMany(10);

        CustomerFactory::createMany(
            5,
            static function(int $i) {
                return ['name' => "Customer $i"];
            }
        );

        ProductFactory::createMany(
            20,
            static function(int $i) {
                return ['name' => "Product $i"];
            }
        );

        $manager->flush();
    }
}
