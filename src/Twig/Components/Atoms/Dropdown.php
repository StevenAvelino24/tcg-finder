<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/atoms/dropdown.html.twig')]
final class Dropdown
{
    public string $id = '';
    public ?string $label = null;
    public array $items = [];
    public ?string $classes = null;
    public ?string $icon = null;
}