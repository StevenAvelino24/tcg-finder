<?php

namespace App\EventSubscriber;

use App\Entity\Shop;
use App\Search\Indexer\ShopIndexer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class ShopESIndexSubscriber implements EventSubscriber
{
    public function __construct(private ShopIndexer $indexer) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->index($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->index($args);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Shop) {
            return;
        }

        $this->indexer->delete($entity->getId());
    }

    protected function index(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Shop) {
            return;
        }

        $this->indexer->index($entity);
    }
}