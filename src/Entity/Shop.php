<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_SLUG', fields: ['slug'])]
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
    #[Assert\Type(type: 'integer', message: 'shop.zipcode.wrong_type')]
    #[Assert\Positive(message: 'shop.zipcode.not_positive')]
    private int $zipcode;

    #[ORM\Column(length: 80)]
    #[Assert\NotBlank(message: 'shop.city.not_blank')]
    private string $city;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: 'shop.state.not_blank')]
    private string $state;

    #[ORM\Column(nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Email(message: 'shop.email.not_email')]
    private ?string $email = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $openingHours;

     #[ORM\OneToMany(
        mappedBy: 'shop',
        targetEntity: Image::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $images;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: 'shop.longitude.not_blank')]
    #[Assert\Type(type: 'float', message: 'shop.longitude.wrong_type')]
    private float $longitude;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: 'shop.latitude.not_blank')]
    #[Assert\Type(type: 'float', message: 'shop.latitude.wrong_type')]
    private float $latitude;

    #[ORM\ManyToMany(targetEntity: Game::class, inversedBy: 'shops')]
    #[ORM\JoinTable(name: 'shop_game')]
    private Collection $games;

    #[ORM\Column(length: 180, unique: true)]
    private string $slug;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[Assert\NotNull(message: 'shop.user.not_null')]
    private User $user;

    #[ORM\Column(type: 'boolean')]
    private bool $selling = false;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->games = new ArrayCollection();
    }

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

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setShop($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getShop() === $this) {
                $image->setShop(null);
            }
        }

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    public function setOpeningHours(?string $openingHours): static
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(Game $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->addShop($this);
        }

        return $this;
    }

    public function removeGame(Game $game): static
    {
        if ($this->games->removeElement($game)) {
            $game->removeShop($this);
        }

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

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

    public function getSelling(): bool
    {
        return $this->selling;
    }

    public function setSelling(bool $selling = false): static
    {
        $this->selling = $selling;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled = false): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
