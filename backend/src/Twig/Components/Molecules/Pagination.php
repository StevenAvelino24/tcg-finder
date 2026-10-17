<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/molecules/pagination.html.twig')]
final class Pagination
{
    public int $page = 1;
    public int $limit = 25;
    public int $total = 0;
    public string $route = '';
    public array $routeParams = [];

    public function getTotalPages(): int
    {
        return max(1, (int) ceil($this->total / max(1, $this->limit)));
    }

    public function getPageRange(): array
    {
        $totalPages = $this->getTotalPages();
        if ($totalPages <= 1) {
            return [];
        }

        $range = [];
        $current = $this->page;

        $range[] = 1;

        $start = max(2, $current - 2);
        $end = min($totalPages - 1, $current + 2);

        if ($start > 2) {
            $range[] = '...';
        }

        for ($i = $start; $i <= $end; $i++) {
            $range[] = $i;
        }

        if ($end < $totalPages - 1) {
            $range[] = '...';
        }

        if ($totalPages > 1) {
            $range[] = $totalPages;
        }

        return $range;
    }
}