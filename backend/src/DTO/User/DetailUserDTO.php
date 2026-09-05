<?php

namespace App\DTO\User;

use App\DTO\Shop\PartialDetailShopDTO;
use App\Entity\Shop;
use App\Entity\User;

final class DetailUserDTO
{
    public function __construct(
        public int $id,
        public string $email,
        public array $shops,
        public string $firstName,
        public ?string $lastName,
        public array $roles,
        public bool $isVerified
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            email: $user->getEmail(),
            shops: array_map(
                static fn (Shop $shop) => PartialDetailShopDTO::fromEntity($shop),
                $user->getShops()->toArray()
            ),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            roles: $user->getRoles(),
            isVerified: $user->getIsVerified()
        );
    }
}