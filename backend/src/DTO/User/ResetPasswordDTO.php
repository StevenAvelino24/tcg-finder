<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints as Assert;

final class ResetPasswordDTO
{
    #[Assert\NotBlank(message: 'auth.reset_token.not_blank')]
    public string $token;

    #[Assert\NotBlank(message: 'auth.password.not_blank')]
    #[Assert\PasswordStrength(message: 'auth.password.not_strong', minScore: PasswordStrength::STRENGTH_WEAK)]
    public string $password;
}