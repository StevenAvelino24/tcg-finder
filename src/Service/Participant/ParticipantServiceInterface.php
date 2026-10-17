<?php

namespace App\Service\Participant;

use App\DTO\Participant\CreateParticipantDTO;
use App\Entity\Event;
use App\Entity\Participant;

interface ParticipantServiceInterface
{
    public function createFromDTO(CreateParticipantDTO $dto, Event $event): Participant;
}