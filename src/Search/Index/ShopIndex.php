<?php

namespace App\Search\Index;

final class ShopIndex
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
                    'opening_hours' => ['type' => 'text'],
                    'phone' => ['type' => 'text'],
                    'email' => ['type' => 'text'],
                    'slug' => ['type' => 'text'],
                    'location' => ['type' => 'geo_point'],
                    'games' => [
                        'type' => 'nested',
                        'properties' => [
                            'name' => ['type' => 'keyword'],
                        ],
                    ],
                    'images' => [
                        'type' => 'nested',
                        'properties' => [
                            'url' => ['type' => 'text'],
                            'alt' => ['type' => 'text'],
                            'position' => ['type' => 'integer'],
                        ],
                    ],
                    'selling' => ['type' => 'boolean'],
                    'description' => ['type' => 'text'],
                ],
            ],
        ];
    }
}
