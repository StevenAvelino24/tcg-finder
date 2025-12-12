<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'shop.title.not_blank')]
    private string $title;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'shop.address.not_blank')]
    private string $address;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'shop.zipcode.not_blank')]
    #[Assert\Type(type: 'integer', message: 'shop.zipcode_not_correct_type')]
    #[Assert\Positive(message: 'shop.zipcode.not_positive')]
    private int $zipcode;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank(message: 'shop.city.not_blank')]
    private string $city;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: 'shop.state.not_blank')]
    private string $state;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipcode(): int
    {
        return $this->zipcode;
    }

    public function setZipcode(int $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }
}
