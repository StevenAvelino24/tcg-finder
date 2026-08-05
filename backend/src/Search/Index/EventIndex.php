<?php

namespace App\Search\Index;

final class EventIndex
{
    public const NAME = 'events_v1';

    public static function mapping(): array
    {
        return [
            'mappings' => [
                'properties' => [
                    'name' => ['type' => 'text'],
                    'game' => ['type' => 'keyword'],
                    'format' => ['type' => 'text'],
                    'entry_cost' => ['type' => 'text'],
                    'description' => ['type' => 'text'],
                    'number_participants' => ['type' => 'integer'],
                    'location' => ['type' => 'geo_point'],
                    'shop' => [
                        'type' => 'nested',
                        'properties' => [
                            'title' => ['type' => 'text'],
                            'slug' => ['type' => 'text'],
                            'address' => ['type' => 'text'],
                            'city' => ['type' => 'text'],
                            'zipcode' => ['type' => 'text'],
                            'state' => ['type' => 'text'],
                        ],
                    ],
                    'start_date_time' => ['type' => 'date'],
                    'end_date_time' => ['type' => 'date'],
                    'slug' => ['type' => 'text'],
                ],
            ],
        ];
    }
}