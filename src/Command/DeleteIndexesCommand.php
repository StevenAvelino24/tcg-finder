<?php

namespace App\Command;

use App\Search\Index\EventIndex;
use App\Search\Index\ShopIndex;
use Elastic\Elasticsearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('app:es:delete-indexes')]
class DeleteIndexesCommand extends Command
{
    public function __construct(private Client $client)
    {
        parent::__construct();
    }

    protected function execute($input, $output): int
    {
        $this->deleteIndex(ShopIndex::NAME);
        $this->deleteIndex(EventIndex::NAME);

        return Command::SUCCESS;
    }

    protected function deleteIndex(string $name): void
    {
        if (!$this->client->indices()->exists(['index' => $name])->asBool()) {
            return;
        }

        $this->client->indices()->delete(['index' => $name]);
    }
}