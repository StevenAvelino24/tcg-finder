<?php

namespace App\Service\Search;

interface SearchServiceInterface
{
    public function search(array $filters, string $index, array $geoBox, int $limit = 20): array;
}