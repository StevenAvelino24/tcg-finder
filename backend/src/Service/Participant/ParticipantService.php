<?php

namespace App\Service\Participant;

use App\DTO\Participant\CreateParticipantDTO;
use App\Entity\Event;
use App\Entity\Participant;

final class ParticipantService implements ParticipantServiceInterface
{
    public function createFromDTO(CreateParticipantDTO $dto, Event $event): Participant
    {
        $participant = new Participant();

        $participant->setFirstName($dto->firstName);
        $participant->setLastName($dto->lastName);
        $participant->setEmail($dto->email);
        $participant->setEvent($event);
        $participant->setUnregisterToken(bin2hex(random_bytes(32)));

        return $participant;
    }
}