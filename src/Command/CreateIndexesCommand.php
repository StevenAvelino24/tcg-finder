<?php

namespace App\Command;

use App\Search\Index\ShopIndex;
use Elastic\Elasticsearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('app:es:create-shop-index')]
class CreateIndexesCommand extends Command
{
    public function __construct(private Client $client)
    {
        parent::__construct();
    }

    protected function execute($input, $output): int
    {
        if ($this->client->indices()->exists(['index' => ShopIndex::NAME])->asBool()) {
            return Command::SUCCESS;
        }

        $this->client->indices()->create([
            'index' => ShopIndex::NAME,
            'body' => ShopIndex::mapping(),
        ]);

        return Command::SUCCESS;
    }
}