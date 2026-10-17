<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/atoms/link.html.twig')]
final class Link
{
    public string $href = '#';
    public string $color = 'primary';
    public string $textSize = 'md';
    public ?string $classes = null;

    public function mount(string $color = 'primary', string $textSize = 'md'): void
    {
        $colorsAllowed = ['primary', 'secondary', 'accent'];
        $textSizesAllowed = ['sm', 'md', 'lg'];

        if (!in_array($color, $colorsAllowed, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid value for argument color for link : %s', $color));
        }

        if (!in_array($textSize, $textSizesAllowed, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid value for argument textSize : %s', $textSize));
        }

        $this->color = $color;
        $this->textSize = $textSize;
    }
}