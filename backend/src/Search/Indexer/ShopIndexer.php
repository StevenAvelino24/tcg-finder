<?php

namespace App\Search\Indexer;

use App\Entity\Shop;
use App\Search\Transformer\ShopDocumentTransformer;
use App\Search\Index\ShopIndex;
use Elastic\Elasticsearch\Client;

final class ShopIndexer
{
    public function __construct(
        private Client $client,
        private ShopDocumentTransformer $transformer
    ) {}

    public function index(Shop $shop): void
    {
        $this->client->index([
            'index' => ShopIndex::NAME,
            'id' => $shop->getId(),
            'body' => $this->transformer->transform($shop),
        ]);
    }

    public function delete(int $shopId): void
    {
        $this->client->delete([
            'index' => ShopIndex::NAME,
            'id' => $shopId,
        ]);
    }
}