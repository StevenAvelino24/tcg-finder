<?php

namespace App\Tests\Api;

use App\Tests\AuthWebTestCase;
use App\Repository\UserRepository;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class AuthenticationControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testRegisterMissingPasswordPayload(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/register',
            [
                'email' => 'admin',
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('password', $data['errors']);
    }

    public function testRegisterWithWrongEmailFormat(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/register',
            [
                'email' => 'admin',
                'password' => 'Admin1234**',
                'firstName' => 'Test',
                'lastName' => 'Test'
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertArrayNotHasKey('password', $data['errors']);
    }

    public function testRegisterWithPasswordNotStrongEnough(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/register',
            [
                'email' => 'admin@admin.ch',
                'password' => 'admin',
                'firstName' => 'Test',
                'lastName' => 'Test'
            ]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('password', $data['errors']);
    }

    public function TestRegisterWithUserAlreadyExists(): void
    {
        UserFactory::createOne([
            'email' => 'admin@admin.ch',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'Test'
        ]);

        $this->client->jsonRequest(
            'POST',
            '/api/register',
            [
                'email' => 'admin@admin.ch',
                'password' => 'Admin1234**',
                'firstName' => 'Test',
                'lastName' => 'Test'
            ]
        );

        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterSuccess(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/register',
            [
                'email' => 'admin@admin.ch',
                'password' => 'Admin1234**',
                'firstName' => 'Test',
                'lastName' => 'Test'
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $user = $this->userRepository->findOneBy(['email' => 'admin@admin.ch']);

        $this->assertNotNull($user);
        $this->assertNotSame('Admin1234**', $user->getPassword());
    }

    public function testRegisterSendsVerificationEmail(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/register',
            [
                'email' => 'admin@admin.ch',
                'password' => 'Admin1234**',
                'firstName' => 'Test',
                'lastName' => 'Test',
                'isVerified' => false
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $user = $this->userRepository->findOneBy(['email' => 'admin@admin.ch']);

        $this->assertNotNull($user);
        $this->assertFalse($user->getIsVerified());

        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();

        $this->assertEmailHeaderSame($email, 'To', 'admin@admin.ch');
        $this->assertEmailHtmlBodyContains($email, 'verify');

        $htmlBody = $email->getHtmlBody();
        preg_match('/href="([^"]+)"/', $htmlBody, $matches);
        $signedUrl = html_entity_decode($matches[1]);

        $parsedUrl = parse_url($signedUrl);
        $this->client->jsonRequest('GET', $parsedUrl['path'] . '?' . $parsedUrl['query']);

        $this->assertResponseIsSuccessful();

        $user = $this->userRepository->findOneBy(['email' => 'admin@admin.ch']);
        $this->assertTrue($user->getIsVerified());
    }

    public function testResendVerificationEmailReturnsErrorIfNoUser(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/api/resend_verify_email?id=34892'
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testResendVerificationEmailReturnsErrorIfUserAlreadyVerified(): void
    {
        $user = UserFactory::createOne([
            'email' => 'admin@admin.ch',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'Test',
            'isVerified' => true
        ]);

        $this->client->jsonRequest(
            'GET',
            '/api/resend_verify_email?id=' . (string) $user->getId()
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testResendVerificationEmailSendsEmailIfUserNotVerified(): void
    {
        $user = UserFactory::createOne([
            'email' => 'admin@admin.ch',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'Test',
            'isVerified' => false
        ]);

        $this->client->jsonRequest(
            'GET',
            '/api/resend_verify_email?id=' . (string) $user->getId()
        );

        $this->assertResponseStatusCodeSame(200);

        $this->assertEmailCount(1);

        $email = $this->getMailerMessage();

        $this->assertEmailHeaderSame($email, 'To', 'admin@admin.ch');
        $this->assertEmailHtmlBodyContains($email, 'verify');
    }

    public function testMeWithNoToken(): void
    {
        $this->client->jsonRequest('GET','/api/backend/user');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeWithNoValidUser(): void
    {
        $this->client->jsonRequest(
            'GET',
            '/api/backend/user',
            server: [
                'HTTP_AUTHORIZATION' => 'Bearer Test1234',
            ]
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeReturnsCurrentUser(): void
    {
        $this->authenticate('user@admin.ch', 'Password1234');

        $this->client->jsonRequest(
            'GET',
            '/api/backend/user'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('user@admin.ch', $data['email']);
    }

    public function testUpdateReturnsUserWithNewData(): void
    {
        $this->authenticate('user@admin.ch', 'Password1234');

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/user',
            [
                'email' => 'user@admin.ch',
                'firstName' => 'Steven',
                'lastName' => 'Avelino'
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $user = $this->userRepository->findOneBy(['email' => 'user@admin.ch']);
        $this->assertSame('Steven', $user->getFirstName());
        $this->assertSame('Avelino', $user->getLastName());
    }

    public function testDeleteRemovesUserFromDatabase(): void
    {
        $this->authenticate('user@admin.ch', 'Password1234');

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/user'
        );

        $this->assertResponseStatusCodeSame(204);
        $user = $this->userRepository->findOneBy(['email' => 'user@admin.ch']);
        $this->assertNull($user);
    }
}