<?php

namespace App\Search\Indexer;

use App\Entity\Event;
use App\Search\Index\EventIndex;
use App\Search\Transformer\EventDocumentTransformer;
use Elastic\Elasticsearch\Client;

final class EventIndexer
{
    public function __construct(
        private Client $client,
        private EventDocumentTransformer $transformer,
    ) {}

    public function index(Event $event): void
    {
        $this->client->index([
            'index' => EventIndex::NAME,
            'id' => $event->getId(),
            'body' => $this->transformer->transform($event),
            'refresh' => true,
        ]);
    }

    public function delete(int $eventId): void
    {
        $this->client->delete([
            'index' => EventIndex::NAME,
            'id' => $eventId,
            'refresh' => true,
        ]);
    }
}