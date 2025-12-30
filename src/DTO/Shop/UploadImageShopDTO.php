<?php

namespace App\DTO\Shop;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class UploadImageShopDTO
{
    #[Assert\NotNull]
    #[Assert\Image(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp']
    )]
    public ?UploadedFile $file = null;

    public ?string $alt;

    public int $position;
}