<?php

namespace App\DTO\Image;

use App\Entity\Image;

final class DetailImageDTO
{
    public function __construct(
        public int $id,
        public string $url,
        public ?string $alt,
        public int $position
    ) {}

    public static function fromEntity(Image $image): self
    {
        return new self(
            id: $image->getId(),
            alt: $image->getAlt(),
            position: $image->getPosition(),
            url: '/uploads/shops/' . $image->getFileName()
        );
    }
}