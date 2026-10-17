<?php

namespace App\Factory;

use App\Entity\Shop;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Shop>
 */
final class ShopFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Shop::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'address' => self::faker()->address(),
            'city' => self::faker()->city(),
            'latitude' => self::faker()->latitude(),
            'longitude' => self::faker()->longitude(),
            'openingHours' => self::faker()->text(),
            'slug' => self::faker()->unique()->slug(3),
            'state' => 'VD',
            'title' => self::faker()->sentence(3),
            'user' => UserFactory::new(),
            'zipcode' => 1000,
            'selling' => false,
            'enabled' => true
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Shop $shop): void {})
        ;
    }
}
