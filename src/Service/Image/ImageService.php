<?php

namespace App\Service\Image;

use App\DTO\Shop\UploadImageShopDTO;
use App\Entity\Image;
use App\Entity\Shop;

final class ImageService implements ImageServiceInterface
{
    public function createFromDTO(UploadImageShopDTO $dto, Shop $shop): ?Image
    {
        if ($shop->getImages()->count() >= 3) {
            return null;
        }
        
        $image = new Image();
        $image->setImageFile($dto->file);
        $image->setAlt($dto->alt);
        $image->setPosition($dto->position);
        $image->setShop($shop);

        return $image;
    }
}