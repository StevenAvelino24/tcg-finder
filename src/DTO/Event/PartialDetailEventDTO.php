<?php

namespace App\DTO\Event;

use App\DTO\Game\DetailGameDTO;
use App\Entity\Event;
use DateTimeImmutable;

final class PartialDetailEventDTO
{
    public function __construct(
        public string $name,
        public DetailGameDTO $game,
        public string $format,
        public int $numberParticipants,
        public DateTimeImmutable $startDateTime,
        public DateTimeImmutable $endDateTime,
        public string $slug
    ) {}

    public static function fromEntity(Event $event): self
    {
        return new self(
            name: $event->getName(),
            game: DetailGameDTO::fromEntity($event->getGame()),
            format: $event->getFormat(),
            numberParticipants: $event->getNumberParticipants(),
            startDateTime: $event->getStartDateTime(),
            endDateTime: $event->getEndDateTime(),
            slug: $event->getSlug()
        );
    }
}