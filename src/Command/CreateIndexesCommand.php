<?php

namespace App\Command;

use App\Search\Index\EventIndex;
use App\Search\Index\ShopIndex;
use Elastic\Elasticsearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('app:es:create-indexes')]
class CreateIndexesCommand extends Command
{
    public function __construct(private Client $client)
    {
        parent::__construct();
    }

    protected function execute($input, $output): int
    {
        $this->createIndex(ShopIndex::NAME, ShopIndex::mapping());
        $this->createIndex(EventIndex::NAME, EventIndex::mapping());

        return Command::SUCCESS;
    }

    protected function createIndex(string $name, array $mapping): void
    {
        if ($this->client->indices()->exists(['index' => $name])->asBool()) {
            return;
        }

        $this->client->indices()->create([
            'index' => $name,
            'body' => $mapping,
        ]);
    }
}