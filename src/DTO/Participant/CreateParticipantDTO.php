<?php

namespace App\DTO\Participant;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateParticipantDTO
{
    #[Assert\NotBlank(message: 'participant.first_name.not_blank')]
    public string $firstName;

    #[Assert\NotBlank(message: 'participant.last_name.not_blank')]
    public string $lastName;

    #[Assert\NotBlank(message: 'participant.email.not_blank')]
    #[Assert\Email(message: 'participant.email.wrong_format')]
    public string $email;
}