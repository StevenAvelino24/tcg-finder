<?php

namespace App\Service\Event;

use App\DTO\Event\CreateEventDTO;
use App\Entity\Event;
use App\Entity\Shop;

interface EventServiceInterface
{
    public function createFromDTO(CreateEventDTO $dto): ?Event;
    public function updateFromDTO(CreateEventDTO $dto, Event $event): ?Event;
}