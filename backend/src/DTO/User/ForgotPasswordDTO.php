<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final class ForgotPasswordDTO
{
    #[Assert\NotBlank(message: 'auth.email.not_blank')]
    #[Assert\Email(message: 'auth.email.wrong_format')]
    public string $email;
}