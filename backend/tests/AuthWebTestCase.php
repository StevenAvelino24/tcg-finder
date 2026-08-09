<?php

namespace App\Tests;

use App\Factory\UserFactory;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AuthWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
    }

    protected function authenticate(
        string $username,
        string $password,
        array $roles = ['ROLE_USER'],
        string $firstName = 'Test',
        string $lastName = 'Test',
        bool $isVerified = true
    ): void
    {
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        UserFactory::createOne([
            'email' => $username,
            'password' => $hasher->hashPassword(new User(), $password),
            'roles' => $roles,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'isVerified' => $isVerified
        ]);

        $this->client->jsonRequest(
            'POST',
            '/api/login_check',
            [
                'username' => $username,
                'password' => $password,
            ]
        );
        
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $data['token']));
    }
}