<?php

namespace App\DTO\Shop;

use App\DTO\Event\PartialDetailEventDTO;
use App\DTO\Game\DetailGameDTO;
use App\DTO\Image\DetailImageDTO;
use App\Entity\Game;
use App\Entity\Image;
use App\Entity\Shop;
use DateTimeImmutable;

final class DetailShopDTO
{
    public function __construct(
        public int $id,
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
        public array $events,
        public array $games,
        public array $images,
        public bool $selling,
        public ?string $description,
        public bool $enabled,
    ) {}

    public static function fromEntity(Shop $shop): self
    {
        $futureEvents = [];

        foreach ($shop->getEvents() as $event) {
            if ($event->getStartDateTime() > new DateTimeImmutable()) {
                $futureEvents[] = PartialDetailEventDTO::fromEntity($event);
            }
        }

        return new self(
            id: $shop->getId(),
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
            enabled: $shop->getEnabled(),
            events: $futureEvents,
            games: array_map(
                static fn (Game $game) => DetailGameDTO::fromEntity($game),
                $shop->getGames()->toArray()
            ),
            images: array_map(
                static fn (Image $image) => DetailImageDTO::fromEntity($image),
                $shop->getImages()->toArray()
            )
        );
    }
}