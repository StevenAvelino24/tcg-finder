<?php

namespace App\Service\User;

use App\Entity\User;

interface UserServiceInterface
{
    public function sendVerificationEmail(string $userId, string $userEmail): void;

    public function sendForgotPasswordEmail(User $user): void;
}