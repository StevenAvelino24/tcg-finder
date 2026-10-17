<?php

namespace App\Service\Image;

use App\Entity\Shop;
use App\DTO\Shop\UploadImageShopDTO;
use App\Entity\Image;

interface ImageServiceInterface
{
    public function createFromDTO(UploadImageShopDTO $dto, Shop $shop): ?Image;
}