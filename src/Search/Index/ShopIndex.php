<?php

namespace App\Search\Index;

class ShopIndex
{
    public const NAME = 'shops_v1';

    public static function mapping(): array
    {
        return [
            'mappings' => [
                'properties' => [
                    'title' => ['type' => 'text'],
                    'address' => ['type' => 'text'],
                    'city' => ['type' => 'keyword'],
                    'state' => ['type' => 'keyword'],
                    'zipcode' => ['type' => 'keyword'],
                    'openingHours' => ['type' => 'text'],
                    'slug' => ['type' => 'keyword'],
                    'location' => ['type' => 'geo_point'],
                    'games' => [
                        'type' => 'nested',
                        'properties' => [
                            'id' => ['type' => 'keyword'],
                            'name' => ['type' => 'text'],
                        ],
                    ],
                ],
            ],
        ];
    }
}
