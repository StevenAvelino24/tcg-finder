<?php

namespace App\EventSubscriber;

use App\Entity\Participant;
use App\Message\ParticipantCreatedMessage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

final class ParticipantCreatedSubscriber implements EventSubscriber
{
    public function __construct(private MessageBusInterface $messageBus) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Participant) {
            return;
        }

        $this->messageBus->dispatch(
            new ParticipantCreatedMessage(
                firstName: $entity->getFirstName(),
                lastName: $entity->getLastName(),
                token: $entity->getUnregisterToken(),
                email: $entity->getEmail()
            )
        );
    }
}