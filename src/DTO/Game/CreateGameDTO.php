<?php

namespace App\DTO\Game;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateGameDTO
{
    #[Assert\NotBlank(message: 'game.name.not_blank')]
    public string $name;
}