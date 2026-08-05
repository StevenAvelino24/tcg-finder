<?php

namespace App\EventListener;

use App\Entity\Shop;
use App\Search\Indexer\ShopIndexer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Shop::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Shop::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Shop::class)]
final class ShopEntityListener
{
    public function __construct(private ShopIndexer $indexer) {}

    public function postPersist(Shop $shop, LifecycleEventArgs $args): void
    {
        $this->indexer->index($shop);
    }

    public function postUpdate(Shop $shop, LifecycleEventArgs $args): void
    {
        $this->indexer->index($shop);
    }

    public function preRemove(Shop $shop, LifecycleEventArgs $args): void
    {
        $this->indexer->delete($shop->getId());
    }
}