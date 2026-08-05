<?php

namespace App\Search\Transformer;

use App\Entity\Game;
use App\Entity\Image;
use App\Entity\Shop;

class ShopDocumentTransformer
{
    public function transform(Shop $shop): array
    {
        return [
            'title' => $shop->getTitle(),
            'address' => $shop->getAddress(),
            'city' => $shop->getCity(),
            'state' => $shop->getState(),
            'zipcode' => (string) $shop->getZipcode(),
            'openingHours' => $shop->getOpeningHours(),
            'phone' => $shop->getPhone(),
            'email' => $shop->getEmail(),
            'slug' => $shop->getSlug(),
            'selling' => $shop->getSelling(),
            'description' => $shop->getDescription(),
            'location' => [
                'lat' => $shop->getLatitude(),
                'lon' => $shop->getLongitude(),
            ],
            'games' => array_map(
                fn (Game $game) => [
                    'name' => $game->getName(),
                ],
                $shop->getGames()->toArray()
            ),
            'images' => array_map(
                fn (Image $image) => [
                    'url' => '/uploads/shops/' . $image->getFileName(),
                    'alt' => $image->getAlt(),
                    'position' => $image->getPosition()
                ],
                $shop->getImages()->toArray())
        ];
    }
}
