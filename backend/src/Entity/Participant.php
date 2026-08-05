<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    #[Assert\NotBlank(message: 'participant.first_name.not_blank')]
    private string $firstName;

    #[ORM\Column(nullable: false)]
    #[Assert\NotBlank(message: 'participant.last_name.not_blank')]
    private string $lastName;

    #[ORM\Column(nullable: false)]
    #[Assert\NotBlank(message: 'participant.email.not_blank')]
    #[Assert\Email(message: 'participant.email.wrong_format')]
    private string $email;

    #[ORM\Column(length: 64, unique: true)]
    private string $unregisterToken;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'participant.event.not_null')]
    private Event $event;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setUnregisterToken(string $unregisterToken): static
    {
        $this->unregisterToken = $unregisterToken;

        return $this;
    }

    public function getUnregisterToken(): string
    {
        return $this->unregisterToken;
    }

    public function setEvent(Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
