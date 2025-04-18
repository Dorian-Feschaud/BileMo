<?php

namespace App\Factory;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Product>
 */
final class ProductFactory extends PersistentProxyObjectFactory{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Product::class;
    }

        /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable    {
        $height = self::faker()->randomFloat(2, 120, 200);
        $width = self::faker()->randomFloat(2, 60, 120);
        return [
            'backCamera' => self::faker()->randomElement(['Simple', 'Double', 'Triple']),
            'backCameraResolution' => self::faker()->numberBetween(40, 60),
            'batteryCapacity' => self::faker()->numberBetween(3700, 4200),
            'capacity' => self::faker()->randomElement([64, 128, 256, 512]),
            'color' => self::faker()->colorName(),
            'frontCameraResolution' => self::faker()->numberBetween(10, 20),
            'height' => $height,
            'manufacturer' => self::faker()->randomElement(['Apple', 'Samsung', 'Xiaomi']),
            'name' => "Product X",
            'network' => self::faker()->randomElement(['5G', '4G']),
            'price' => self::faker()->randomFloat(2, 300, 2000),
            'processor' => 'Processor ' . self::faker()->numberBetween(1, 5),
            'ram' => self::faker()->randomElement([8, 12, 16]),
            'releaseDate' => self::faker()->dateTimeBetween('-10 years'),
            'screen' => self::faker()->randomElement(['LED', 'OLED', 'QLED']),
            'screenHeight' => $height - self::faker()->randomFloat(2, 0, 30),
            'screenResolution' => self::faker()->numberBetween(300, 500),
            'screenWidth' => $width - self::faker()->randomFloat(2, 0, 15),
            'thickness' => self::faker()->randomFloat(2, 0.5, 1.5),
            'weight' => self::faker()->numberBetween(100, 300),
            'width' => $width,
        ];
    }

        /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Product $product): void {})
        ;
    }
}
