<?php

namespace App\Service\Shop;

use App\DTO\Shop\CreateShopDTO;
use App\Entity\Shop;
use App\Entity\User;

interface ShopServiceInterface
{
    public function createFromDTO(CreateShopDTO $dto, User $user): Shop;
    public function updateFromDTO(CreateShopDTO $dto, Shop $shop): Shop;
}