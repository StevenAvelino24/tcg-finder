<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/atoms/button.html.twig')]
final class Button
{
    public string $variant = 'primary';
    public string $type = 'button';
    public ?string $classes = null;
    public ?string $href = null;

    public function mount(string $variant, string $type, ?string $classes = null): void
    {
        $allowedVariants = ['primary', 'secondary', 'accent'];
        $allowedTypes = ['button', 'submit', 'reset', 'link'];

        if (!in_array($variant, $allowedVariants, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid value for argument variant : %s', $variant));
        }

        if (!in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid value for argument type : %s', $type));
        }

        $this->variant = $variant;
        $this->type = $type;
        $this->classes = $classes;
    }
}