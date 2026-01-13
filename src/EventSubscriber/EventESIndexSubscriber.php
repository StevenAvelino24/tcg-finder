<?php

namespace App\EventSubscriber;

use App\Entity\Event;
use App\Search\Indexer\EventIndexer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class EventESIndexSubscriber implements EventSubscriber
{
    public function __construct(private EventIndexer $indexer) {}

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

        if (!$entity instanceof Event) {
            return;
        }

        $this->indexer->delete($entity->getId());
    }

    protected function index(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Event) {
            return;
        }

        $this->indexer->index($entity);
    }
}