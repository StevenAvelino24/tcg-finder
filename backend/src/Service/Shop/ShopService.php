<?php

namespace App\Service\Shop;

use App\DTO\Shop\CreateShopDTO;
use App\Entity\Shop;
use App\Entity\User;
use App\Repository\GameRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ShopService implements ShopServiceInterface
{
    public function __construct(
        private GameRepository $gameRepository,
        private SluggerInterface $slugger
    ) {}

    public function createFromDTO(CreateShopDTO $dto, User $user): Shop
    {
        $shop = new Shop();
        $shop->setUser($user);
        $shop->setSlug(
            $this->slugger->slug($dto->title)->lower()
        );

        return $this->map($shop, $dto);
    }

    public function updateFromDTO(CreateShopDTO $dto, Shop $shop): Shop
    {
        $shop = $this->map($shop, $dto);

        return $shop;
    }

    private function map(Shop $shop, CreateShopDTO $dto): Shop
    {
        $shop
            ->setTitle($dto->title)
            ->setAddress($dto->address)
            ->setZipcode($dto->zipcode)
            ->setCity($dto->city)
            ->setState($dto->state)
            ->setPhone($dto->phone)
            ->setEmail($dto->email)
            ->setOpeningHours($dto->openingHours)
            ->setSelling($dto->selling)
            ->setDescription($dto->description);

        $shop->getGames()->clear();
        foreach ($dto->games as $gameId) {
            $game = $this->gameRepository->find($gameId);
            if ($game) {
                $shop->addGame($game);
            }
        }

        return $shop;
    }
}