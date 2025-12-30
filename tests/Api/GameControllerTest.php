<?php

namespace App\Tests\Api;

use App\Factory\GameFactory;
use App\Repository\GameRepository;
use App\Tests\AuthWebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class GameControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private GameRepository $gameRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameRepository = static::getContainer()->get(GameRepository::class);
    }

    public function testCreateReturnsErrorWithNonAdminUser(): void
    {
        $this->authenticate('user@admin.ch', 'password123');

        $this->client->jsonRequest(
            'POST',
            '/api/admin/games',
            ['name' => 'Lorcana']
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testCreateWithNoDataReturnsError(): void
    {
        $this->authenticate('user@admin.ch', 'password123', ['ROLE_ADMIN']);

        $this->client->jsonRequest(
            'POST',
            '/api/admin/games',
            []
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateReturnsSuccessWithCorrectPayload(): void
    {
        $this->authenticate('user@admin.ch', 'password123', ['ROLE_ADMIN']);

        $this->client->jsonRequest(
            'POST',
            '/api/admin/games',
            ['name' => 'Lorcana']
        );

        $this->assertResponseStatusCodeSame(201);

        $this->assertSame($this->gameRepository->count(), 1);
    }

    public function testUpdateWithNoCorrectId(): void
    {
        $this->authenticate('user@admin.ch', 'password123', ['ROLE_ADMIN']);
        GameFactory::createOne([
            'name' => 'Lorcana',
        ]);

        $this->client->jsonRequest(
            'PUT',
            '/api/admin/games/1',
            ['name' => 'Magic']
        );

        $this->assertResponseStatusCodeSame(200);

        $this->assertSame(1, $this->gameRepository->findOneBy(['name' => 'Magic'])->getId());
    }

    public function testDeleteIsSuccessful(): void
    {
        $this->authenticate('user@admin.ch', 'password123', ['ROLE_ADMIN']);
        GameFactory::createOne([
            'name' => 'Lorcana',
        ]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/admin/games/1',
        );

        $this->assertResponseStatusCodeSame(204);

        $this->assertNull($this->gameRepository->findOneBy(['name' => 'Lorcana']));
    }

    public function testGetListReturnsAllGames(): void
    {
        GameFactory::createOne([
            'name' => 'Lorcana',
        ]);
        GameFactory::createOne([
            'name' => 'Magic',
        ]);

        $this->client->jsonRequest(
            'GET',
            '/api/games',
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $data);
    }
}