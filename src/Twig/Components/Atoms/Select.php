<?php

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(template: 'components/atoms/select.html.twig')]
final class Select
{
    public string $id = '';
    public string $name = '';
    public string $label = '';
    public ?string $placeholder = null;
    public array $options = [];
    public mixed $value = null;
    public ?string $classes = null;

    public function mount(
        string $id = '',
        string $name = '',
        ?string $label = null,
        ?string $placeholder = null,
        array $options = [],
        mixed $value = null,
        ?string $classes = null
    ): void {
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->placeholder = $placeholder ?? '';
        $this->options = $options;
        $this->value = $value;
        $this->classes = $classes ?? '';

        foreach ($this->options as $option) {
            if (!isset($option['value'], $option['label'])) {
                throw new \InvalidArgumentException('Each option must have "value" and "label" keys');
            }
        }
    }

    public function isSelected(mixed $optionValue): bool
    {
        return $optionValue == $this->value;
    }
}
