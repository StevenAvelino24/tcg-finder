<?php

namespace App\DTO\Shop;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateShopDTO
{
    #[Assert\NotBlank(message: 'shop.title.not_blank')]
    #[Assert\Length(max: 150)]
    public string $title;

    #[Assert\NotBlank(message: 'shop.address.not_blank')]
    #[Assert\Length(max: 150)]
    public string $address;

    #[Assert\NotBlank(message: 'shop.city.not_blank')]
    #[Assert\Length(max: 80)]
    public string $city;

    #[Assert\NotBlank(message: 'shop.state.not_blank')]
    #[Assert\Length(max: 40)]
    public string $state;

    #[Assert\NotBlank(message: 'shop.zipcode.not_blank')]
    #[Assert\Positive]
    public int $zipcode;

    public ?string $openingHours = null;

    public ?string $phone = null;

    #[Assert\Email]
    public ?string $email = null;

    public bool $selling = false;

    public ?string $description = null;

    /**
     * Game IDs
     *
     * @var int[]
     */
    #[Assert\All(
        new Assert\Positive(message: 'shop.games.invalid_id')
    )]
    public array $games = [];

    /**
     * Image IDs
     *
     * @var int[]
     */
    #[Assert\All(
        new Assert\Positive(message: 'shop.images.invalid_id')
    )]
    public array $images = [];
}
