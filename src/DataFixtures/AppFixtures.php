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
        $this->loadCustomers();
        $this->loadUsers();
        $this->loadProducts();

        $manager->flush();
    }

    protected function loadCustomers():void
    {
        // SuperAdmin Customer
        CustomerFactory::createOne(
            static function() {
                return [
                    'name' => "SuperAdmin Customer",
                ];
            }
        );

        // Regular Customers
        CustomerFactory::createMany(
            5,
            static function(int $i) {
                return ['name' => "Customer $i"];
            }
        );
    }

    protected function loadUsers():void
    {
        // SuperAdmin User
        UserFactory::createOne(
            static function() {
                return [
                    'firstname' => "Super",
                    'lastname' => "Admin",
                    'email' => "superadmin@example.com",
                    'roles' => ["ROLE_SUPER_ADMIN"],
                    'customer' => CustomerFactory::find(['name' => "SuperAdmin Customer"])
                ];
            }
        );

        // Admin Users
        UserFactory::createMany(
            5,
            static function(int $i) {
                return [
                    'roles' => ["ROLE_ADMIN"],
                    'customer' => CustomerFactory::find(['name' => "Customer $i"])
                ];
            }
        );

        // Regular Users
        UserFactory::createMany(
            10,
            static function() {
                return [
                    'roles' => ["ROLE_USER"],
                    'customer' => CustomerFactory::random()
                ];
            }
        );
    }

    protected function loadProducts():void
    {
        // Regular Products
        ProductFactory::createMany(
            20,
            static function(int $i) {
                return [
                    'name' => "Product $i",
                    'customers' => CustomerFactory::randomRange(0, 5)
                ];
            }
        );
    }
}
