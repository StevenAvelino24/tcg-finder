<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Search\Indexer\EventIndexer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Event::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Event::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Event::class)]
final class EventEntityListener
{
    public function __construct(private EventIndexer $indexer) {}

    public function postPersist(Event $event, LifecycleEventArgs $args): void
    {
        $this->indexer->index($event);
    }

    public function postUpdate(Event $event, LifecycleEventArgs $args): void
    {
        $this->indexer->index($event);
    }

    public function preRemove(Event $event, LifecycleEventArgs $args): void
    {
        $this->indexer->delete($event->getId());
    }
}