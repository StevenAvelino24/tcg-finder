<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'event.name.not_blank')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'event.game.not_null')]
    private Game $game;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'event.format.not_blank')]
    private string $format;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'event.entry_cost.not_blank')]
    private string $entryCost;

    #[ORM\ManyToOne(targetEntity: Shop::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'event.shop.not_null')]
    private Shop $shop;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'event.description.not_blank')]
    private string $description;

    #[ORM\Column]
    #[Assert\NotNull(message: 'event.number_participants.not_null')]
    private int $numberParticipants;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'event.start.not_null')]
    private DateTimeImmutable $startDateTime;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull(message: 'event.end.not_null')]
    private DateTimeImmutable $endDateTime;

    #[ORM\Column(length: 180, unique: true)]
    private string $slug;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getEntryCost(): string
    {
        return $this->entryCost;
    }

    public function setEntryCost(string $entryCost): static
    {
        $this->entryCost = $entryCost;

        return $this;
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNumberParticipants(): int
    {
        return $this->numberParticipants;
    }

    public function setNumberParticipants(int $numberParticipants): static
    {
        $this->numberParticipants = $numberParticipants;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getStartDateTime(): DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(DateTimeImmutable $dateTime): static
    {
        $this->startDateTime = $dateTime;

        return $this;
    }

    public function getEndDateTime(): DateTimeImmutable
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(DateTimeImmutable $dateTime): static
    {
        $this->endDateTime = $dateTime;

        return $this;
    }
}
