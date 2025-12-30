<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateUserDTO
{
    #[Assert\NotBlank(message: 'auth.email.not_blank')]
    #[Assert\Email(message: 'auth.email.wrong_format')]
    public string $email;

    #[Assert\NotBlank(message: 'auth.first_name.not_blank')]
    public string $firstName;

    #[Assert\NotBlank(message: 'auth.last_name.not_blank')]
    public string $lastName;
}