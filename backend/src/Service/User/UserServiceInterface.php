<?php

namespace App\Service\User;

interface UserServiceInterface
{
    public function sendVerificationEmail(string $userId, string $userEmail): void;
}