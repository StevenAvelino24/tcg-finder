<?php

namespace App\Factory;

use App\Entity\Event;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Event>
 */
final class EventFactory extends PersistentObjectFactory
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
        return Event::class;
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
            'description' => self::faker()->text(),
            'endDateTime' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'entryCost' => self::faker()->text(),
            'format' => self::faker()->text(),
            'game' => GameFactory::new(),
            'name' => self::faker()->text(),
            'numberParticipants' => self::faker()->randomNumber(),
            'shop' => ShopFactory::new(),
            'slug' => self::faker()->text(180),
            'startDateTime' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Event $event): void {})
        ;
    }
}
