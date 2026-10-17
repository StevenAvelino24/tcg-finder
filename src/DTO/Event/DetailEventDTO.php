<?php

namespace App\DTO\Event;

use App\DTO\Game\DetailGameDTO;
use App\DTO\Participant\DetailParticipantDTO;
use App\DTO\Shop\PartialDetailShopDTO;
use App\Entity\Event;
use App\Entity\Participant;
use DateTimeImmutable;

final class DetailEventDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public DetailGameDTO $game,
        public string $format,
        public string $entryCost,
        public PartialDetailShopDTO $shop,
        public string $description,
        public int $numberParticipants,
        public DateTimeImmutable $startDateTime,
        public DateTimeImmutable $endDateTime,
        public string $slug,
        public array $participants,
        public int $currentNumberParticipants
    ) {}

    public static function fromEntity(Event $event): self
    {
        return new self(
            id: $event->getId(),
            name: $event->getName(),
            game: DetailGameDTO::fromEntity($event->getGame()),
            format: $event->getFormat(),
            entryCost: $event->getEntryCost(),
            shop: PartialDetailShopDTO::fromEntity($event->getShop()),
            description: $event->getDescription(),
            numberParticipants: $event->getNumberParticipants(),
            startDateTime: $event->getStartDateTime(),
            endDateTime: $event->getEndDateTime(),
            slug: $event->getSlug(),
            participants: array_map(
                static fn (Participant $participant) => DetailParticipantDTO::fromEntity($participant),
                $event->getParticipants()->toArray()
            ),
            currentNumberParticipants: $event->getNumberParticipants() - $event->getParticipants()->count(),
        );
    }
}