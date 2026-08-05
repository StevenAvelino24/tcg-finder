<?php

namespace App\DTO\User;

use App\Entity\User;

final class DetailUserDTO
{
    public function __construct(
        public string $email,
        public bool $hasShop,
        public string $firstName,
        public string $lastName,
        public array $roles,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            email: $user->getEmail(),
            hasShop: $user->getShop() !== null,
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            roles: $user->getRoles(),
        );
    }
}