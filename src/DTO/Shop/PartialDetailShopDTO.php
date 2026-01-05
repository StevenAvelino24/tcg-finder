<?php

namespace App\DTO\Shop;

use App\DTO\Image\DetailImageDTO;
use App\Entity\Image;
use App\Entity\Shop;

final class PartialDetailShopDTO
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $address,
        public string $city,
        public string $state,
        public int $zipcode,
        public ?string $openingHours,
        public ?string $phone,
        public ?string $email,
        public array $images,
        public bool $selling
    ) {}

    public static function fromEntity(Shop $shop): self
    {
        return new self(
            slug: $shop->getSlug(),
            title: $shop->getTitle(),
            address: $shop->getAddress(),
            city: $shop->getCity(),
            state: $shop->getState(),
            zipcode: $shop->getZipcode(),
            openingHours: $shop->getOpeningHours(),
            phone: $shop->getPhone(),
            email: $shop->getEmail(),
            selling: $shop->getSelling(),
            images: array_map(
                static fn (Image $image) => DetailImageDTO::fromEntity($image),
                $shop->getImages()->toArray()
            )
        );
    }
}