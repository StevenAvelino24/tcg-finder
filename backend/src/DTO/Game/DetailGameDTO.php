<?php

namespace App\DTO\Game;

use App\Entity\Game;

final class DetailGameDTO
{
    public function __construct(public int $id, public string $name) {}

    public static function fromEntity(Game $game): self
    {
        return new self(id: $game->getId(), name: $game->getName());
    }
}