<?php

namespace App\Service\Event;

use App\DTO\Event\CreateEventDTO;
use App\Entity\Event;
use App\Entity\Shop;
use App\Repository\GameRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

final class EventService implements EventServiceInterface
{
    public function __construct(
        private GameRepository $gameRepository,
        private SluggerInterface $slugger
    ) {}

    public function createFromDTO(CreateEventDTO $dto, Shop $shop): ?Event
    {
        $event = new Event();
        $event->setShop($shop);
        $event->setSlug(
            $this->slugger->slug($dto->name)->lower()
        );

        return $this->map($dto, $event);
    }

    public function updateFromDTO(CreateEventDTO $dto, Event $event): ?Event
    {
        return $this->map($dto, $event);

        return $event;
    }

    private function map(CreateEventDTO $dto, Event $event): ?Event
    {
        $event
            ->setName($dto->name)
            ->setFormat($dto->format)
            ->setEntryCost($dto->entryCost)
            ->setDescription($dto->description)
            ->setNumberParticipants($dto->numberParticipants)
            ->setStartDateTime($dto->startDateTime)
            ->setEndDateTime($dto->endDateTime);

        $game = $this->gameRepository->findOneBy(['id' => $dto->gameId]);

        if (!$game) {
            return null;
        }

        $event->setGame($game);

        return $event;
    }
}