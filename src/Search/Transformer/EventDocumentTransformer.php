<?php

namespace App\Search\Transformer;

use App\Entity\Event;

final class EventDocumentTransformer
{
    public function transform(Event $event): array
    {
        $shop = $event->getShop();

        return [
            'name' => $event->getName(),
            'game' => $event->getGame()->getName(),
            'format' => $event->getFormat(),
            'entry_cost' => $event->getEntryCost(),
            'description' => $event->getDescription(),
            'number_participants' => $event->getNumberParticipants(),
            'start_date_time' => $event->getStartDateTime()->format('c'),
            'end_date_time' => $event->getEndDateTime()->format('c'),
            'slug' => $event->getSlug(),
            'location' => [
                'lat' => $shop->getLatitude(),
                'lon' => $shop->getLongitude(),
            ],
            'shop' => [
                'title' => $shop->getTitle(),
                'slug' => $shop->getSlug(),
                'address' => $shop->getAddress(),
                'city' => $shop->getCity(),
                'zipcode' => $shop->getZipcode(),
                'state' => $shop->getState(),
            ]
        ];
    }
}