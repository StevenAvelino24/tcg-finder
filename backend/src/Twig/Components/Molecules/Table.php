<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/molecules/table.html.twig')]
final class Table
{
    public array $headings = [];

    public ?string $classes = null;

    public function mount(array $headings = [], ?string $classes = null): void
    {
        $this->headings = $headings;
        $this->classes = $classes ?? '';
    }
}