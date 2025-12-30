<?php

namespace App\Search\Transformer;

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
            'slug' => $shop->getSlug(),
            'location' => [
                'lat' => $shop->getLatitude(),
                'lon' => $shop->getLongitude(),
            ],
            'games' => array_map(
                fn ($game) => [
                    'id' => (string) $game->getId(),
                    'name' => $game->getName(),
                ],
                $shop->getGames()->toArray()
            ),
        ];
    }
}
