<?php

namespace App\Message;

final class ParticipantCreatedMessage
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $token,
        public string $email
    ) {}
}