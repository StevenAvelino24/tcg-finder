<?php

namespace App\DTO\Event;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateEventDTO
{
    #[Assert\NotBlank(message: 'event.name.not_blank')]
    public string $name;

    #[Assert\NotNull(message: 'event.game.not_null')]
    public int $gameId;

    #[Assert\NotNull(message: 'event.shop.not_null')]
    public int $shopId;

    #[Assert\NotBlank(message: 'event.format.not_blank')]
    public string $format;

    #[Assert\NotBlank(message: 'event.entry_cost.not_blank')]
    public string $entryCost;

    #[Assert\NotBlank(message: 'event.description.not_blank')]
    public string $description;

    #[Assert\NotNull(message: 'event.number_participants.not_null')]
    public int $numberParticipants;

    #[Assert\NotNull(message: 'event.start.not_null')]
    public DateTimeImmutable $startDateTime;

    #[Assert\NotNull(message: 'event.end.not_null')]
    public DateTimeImmutable $endDateTime;
}