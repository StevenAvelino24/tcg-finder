<?php

namespace App\DTO\Shop;

use App\Entity\Shop;

final class DetailShopDTO
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $address,
        public string $city,
        public string $state,
        public int $zipcode,
        public ?string $openingHours,
        public float $latitude,
        public float $longitude,
        public ?string $phone,
        public ?string $email,
        public array $games,
        public array $images,
        public bool $selling,
        public ?string $description
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
            latitude: $shop->getLatitude(),
            longitude: $shop->getLongitude(),
            phone: $shop->getPhone(),
            email: $shop->getEmail(),
            selling: $shop->getSelling(),
            description: $shop->getDescription(),
            games: array_map(
                static fn ($game) => [
                    'name' => $game->getName(),
                ],
                $shop->getGames()->toArray()
            ),
            images: array_map(
                static fn ($image) => [
                    'url' => $image->getUrl(),
                    'alt' => $image->getAlt(),
                    'position' => $image->getPosition()
                ],
                $shop->getImages()->toArray()
            )
        );
    }
}