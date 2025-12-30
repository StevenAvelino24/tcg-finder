<?php

namespace App\DTO\User;

use App\DTO\Shop\DetailShopDTO;
use App\Entity\Shop;
use App\Entity\User;

final class DetailUserDTO
{
    public function __construct(
        public string $email,
        public ?Shop $shop,
        public string $firstName,
        public string $lastName
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            email: $user->getEmail(),
            shop: $user->getShop() !== null ? DetailShopDTO::fromEntity($user->getShop()) : null,
            firstName: $user->getFirstName(),
            lastName: $user->getLastName()
        );
    }
}