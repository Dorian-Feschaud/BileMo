<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private readonly UserPasswordHasherInterface $userPasswordHasher;
    private readonly Generator $faker;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher) {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = $this->createFaker();
    }

    protected function createFaker(): Generator
    {
        return Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager):void
    {
        $this->loadSuperAdmin($manager);
        $customers = $this->loadCustomers($manager);
        $this->loadProducts($manager, $customers);

        $manager->flush();
    }

    protected function loadSuperAdmin(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('superadmin@example.com');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($user);
    }

    protected function loadCustomers(ObjectManager $manager, int $count = 3): array
    {
        $customers = [];
        for ($i = 0; $i < $count; $i++) {
            $admin = new User();
            $admin->setEmail('admin' . $i . '@example.com');
            $admin->setRoles(['ROLE_ADMIN']);
            $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
            $manager->persist($admin);

            $customer = new Customer();
            $customer->setName('Customer ' . $i);
            $customer->addUser($admin);
            
            for ($j = 0; $j < $count; $j++) {
                $user = new User();
                $user->setEmail('user' . $j . '@customer' . $i . '.com');
                $user->setRoles(['ROLE_USER']);
                $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
                $manager->persist($user);

                $customer->addUser($user);
            }

            $manager->persist($customer);
            $customers[] = $customer;
        }

        return $customers;
    }

    protected function loadProducts(ObjectManager $manager, array $customers, int $count = 20): void
    {
        for ($i = 0; $i < $count; $i++) {
            $product = new Product();
            $product->setName('Product ' . $i);
            $product->setManufacturer($this->faker->randomElement(['Apple', 'Samsung', 'Xiaomi']));
            $product->setReleaseDate($this->faker->dateTimeBetween('-10 years'));
            $product->setPrice($this->faker->randomFloat(2, 300, 2000));
            $product->setColor($this->faker->colorName());
            $product->setCapacity($this->faker->randomElement([64, 128, 256, 512]));
            $height = $this->faker->randomFloat(2, 120, 200);
            $product->setHeight($height);
            $width = $this->faker->randomFloat(2, 60, 120);
            $product->setWidth($width);
            $product->setThickness($this->faker->randomFloat(2, 0.5, 1.5));
            $product->setWeight($this->faker->numberBetween(100, 300));
            $product->setScreen($this->faker->randomElement(['LED', 'OLED', 'QLED']));
            $product->setScreenHeight($height - $this->faker->randomFloat(2, 0, 30));
            $product->setScreenWidth($width - $this->faker->randomFloat(2, 0, 15));
            $product->setScreenResolution($this->faker->numberBetween(300, 500));
            $product->setBackCamera($this->faker->randomElement(['Simple', 'Double', 'Triple']));
            $product->setBackCameraResolution($this->faker->numberBetween(40, 60));
            $product->setFrontCameraResolution($this->faker->numberBetween(10, 20));
            $product->setProcessor('Processor ' . $this->faker->numberBetween(1, 5));
            $product->setRam($this->faker->randomElement([8, 12, 16]));
            $product->setBatteryCapacity($this->faker->numberBetween(3700, 4200));
            $product->setNetwork($this->faker->randomElement(['5G', '4G']));
            
            for ($j = 0; $j < count($customers); $j++) {
                if ($this->faker->boolean()) {
                    $product->addCustomer($customers[$j]);
                }
            }

            $manager->persist($product);
        }
    }
}
