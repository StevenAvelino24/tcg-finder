<?php

namespace App\Service\User;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserService implements UserServiceInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function createFromDTO(CreateUserDTO $dto): User
    {
        $user = new User();
        $user = $this->map($dto, $user);
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $dto->password
        );
        $user->setPassword($hashedPassword);

        return $user;
    }

    public function updateFromDTO(UpdateUserDTO $dto, User $user): User
    {
        return $this->map($dto, $user);
    }

    private function map(CreateUserDTO|UpdateUserDTO $dto, User $user): User
    {
        $user
            ->setEmail($dto->email)
            ->setFirstName($dto->firstName)
            ->setLastName($dto->lastName);

        return $user;
    }
}