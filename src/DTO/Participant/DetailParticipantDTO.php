<?php

namespace App\DTO\Participant;

use App\Entity\Participant;

final class DetailParticipantDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email
    ) {}

    public static function fromEntity(Participant $participant): self
    {
        return new self(
            firstName: $participant->getFirstName(),
            lastName: $participant->getLastName(),
            email: $participant->getEmail()
        );
    }
}