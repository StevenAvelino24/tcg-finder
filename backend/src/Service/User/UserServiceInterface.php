<?php

namespace App\Service\User;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;

interface UserServiceInterface
{
    public function createFromDTO(CreateUserDTO $dto): User;
    public function updateFromDTO(UpdateUserDTO $dto, User $user): User;
    public function resetPassword(User $user, string $password): User;
}