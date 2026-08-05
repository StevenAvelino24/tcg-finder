<?php

namespace App\Tests\Api;

use App\Factory\GameFactory;
use App\Factory\ImageFactory;
use App\Factory\ShopFactory;
use App\Factory\UserFactory;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Search\Index\ShopIndex;
use App\Tests\AuthWebTestCase;
use Elastic\Elasticsearch\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class ShopControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private ShopRepository $shopRepository;
    private UserRepository $userRepository;
    private Client $esClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = static::getContainer()->get(ShopRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->esClient = static::getContainer()->get(Client::class);

        if ($this->esClient->indices()->exists(['index' => ShopIndex::NAME])->asBool()) {
            $this->esClient->indices()->delete(['index' => ShopIndex::NAME]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->esClient->indices()->exists(['index' => ShopIndex::NAME])->asBool()) {
            $this->esClient->indices()->delete(['index' => ShopIndex::NAME]);
        }
        parent::tearDown();
    }

    protected function getAllEsShopDocuments(): array
    {
        $response = $this->esClient->search([
            'index' => ShopIndex::NAME,
            'body'  => [
                'query' => [
                    'match_all' => (object) [],
                ]
            ],
            'size' => 1000,
        ]);

        return array_map(
            fn (array $hit) => $hit['_source'],
            $response['hits']['hits']
        );
    }

    public function testShopListReturnsErrorIfNotAdmin(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234');

        $this->client->jsonRequest(
            'GET',
            '/api/admin/shops'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopListReturnsAllShopsIfAdmin(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_ADMIN']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        ShopFactory::createOne(['user' => $user]);

        $this->client->jsonRequest(
            'GET',
            '/api/admin/shops'
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $data);
    }

    public function testShopEnableReturnsErrorIfNotAdmin(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $this->client->jsonRequest(
            'PUT',
            '/api/admin/shops/' . $shop->getSlug() . '/enable'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopEnableReturnsSuccessIfAdmin(): void
    {
        $this->authenticate('admin@tcg.ch', 'Password1234', ['ROLE_ADMIN']);
        $user = $this->userRepository->findOneBy(['email' => 'admin@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $this->client->jsonRequest(
            'PUT',
            '/api/admin/shops/' . $shop->getSlug() . '/enable'
        );

        $this->assertResponseStatusCodeSame(200);
        $shop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);

        $this->assertTrue($shop->getEnabled());
    }

    public function testShopDisableReturnsErrorIfNotAdmin(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);

        $this->client->jsonRequest(
            'PUT',
            '/api/admin/shops/' . $shop->getSlug() . '/disable'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopDisableReturnsSuccessIfAdmin(): void
    {
        $this->authenticate('admin@tcg.ch', 'Password1234', ['ROLE_ADMIN']);
        $user = $this->userRepository->findOneBy(['email' => 'admin@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => true]);

        $this->client->jsonRequest(
            'PUT',
            '/api/admin/shops/' . $shop->getSlug() . '/disable'
        );

        $this->assertResponseStatusCodeSame(200);
        $shop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);

        $this->assertFalse($shop->getEnabled());
    }

    public function testShopBackendShowReturnsA404IfNotUserOrAdmin(): void
    {
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);

        $this->client->jsonRequest(
            'GET',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopBackendShowReturnsSuccessIfUserIsAdmin(): void
    {
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $this->authenticate('admin@tcg.ch', 'Password1234', ['ROLE_ADMIN']);

        $this->client->jsonRequest(
            'GET',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testShopBackendShowReturnsSuccessIfUserIsTheSame(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $this->client->jsonRequest(
            'GET',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testShopShowReturnsNotFoundIfShopNotEnabled(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $this->client->jsonRequest(
            'GET',
            '/api/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testShopShowReturnsTheCorrectShop(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->client->jsonRequest(
            'GET',
            '/api/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(200);
    }

    public function testShopCreateReturnsErrorIfNoUser(): void
    {
        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops'
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testShopCreateReturnsErrorIfUserAlreadyHasShop(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        ShopFactory::createOne(['user' => $user, 'enabled' => false]);

        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopCreateReturnsValidationErrorIfPayloadNotValid(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops',
            ['title' => 'Test']
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testShopCreateReturnsSuccessWithAllRequiredData(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops',
            [
                'title' => 'Test',
                'address' => 'Chemin de Montelly 24',
                'zipcode' => 1007,
                'city' => 'Lausanne',
                'state' => 'VD',
                'openingHours' => 'ndineide',
                'phone' => '0782637928',
                'email' => 'steven.avelino24@outlook.com',
                'selling' => true,
                'description' => 'Bonjour'
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $shop = $this->shopRepository->findOneBy(['slug' => 'test']);
        $this->assertSame('Test', $shop->getTitle());
    }
    
    public function testShopCreateReturnsSuccessWithGameData(): void
    {
        GameFactory::createOne(['name' => 'Lorcana']);
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops',
            [
                'title' => 'Test',
                'address' => 'Chemin de Montelly 24',
                'zipcode' => 1007,
                'city' => 'Lausanne',
                'state' => 'VD',
                'games' => [1]
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $shop = $this->shopRepository->findOneBy(['slug' => 'test']);
        $this->assertCount(1, $shop->getGames());
        $this->assertSame('Lorcana', $shop->getGames()[0]->getName());
    }

    public function testShopUpdateReturnsIfUserIsNotTheCreator(): void
    {
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopUpdateReturnsErrorIfPayloadNotCorrect(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/shops/' . $shop->getSlug(),
            ['title' => 'Test']
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testShopUpdateReturnsSuccessIfPayloadCorrect(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/shops/' . $shop->getSlug(),
            [
                'title' => 'Test',
                'address' => 'Chemin de Montelly 24',
                'zipcode' => 1007,
                'city' => 'Lausanne',
                'state' => 'VD',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $updatedShop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);
        $this->assertSame('Chemin de Montelly 24', $updatedShop->getAddress());
    }

    public function testShopDeleteReturnsErrorIfUserNotCreator(): void
    {
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopDeleteReturnsSuccessIfUserIsCreator(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(204);
        $updatedShop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);
        $this->assertNull($updatedShop);
    }

    public function testShopUploadImageReturnsErrorIfUserNotCreator(): void
    {
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);

        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops/' . $shop->getSlug() . '/images'
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testShopUploadImageReturnsSuccessIfUserIsCreator(): void
    {
        $fixture = __DIR__ . '/Fixtures/test.png';
        $tmp = sys_get_temp_dir().'/upload_'.uniqid().'.png';
        copy($fixture, $tmp);

        $file = new UploadedFile(
            $tmp,
            'test.png',
            'image/png',
            null,
            true
        );

        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->client->request(
            'POST',
            '/api/backend/shops/' . $shop->getSlug() . '/images',
            ['alt' => 'Test', 'position' => 0],
            ['file' => $file]
        );

        $this->assertResponseStatusCodeSame(201);
        $updatedShop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);
        $this->assertCount(1, $updatedShop->getImages());
        $image = $updatedShop->getImages()->first();
        $this->assertStringEndsWith('.webp', $image->getFileName());
    }

    public function testShopUploadImageReturnsErrorIfMaxImagesInShop(): void
    {
        $fixture = __DIR__ . '/Fixtures/test.png';
        $tmp = sys_get_temp_dir().'/upload_'.uniqid().'.png';
        copy($fixture, $tmp);

        $file = new UploadedFile(
            $tmp,
            'test.png',
            'image/png',
            null,
            true
        );

        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);
        ImageFactory::createMany(3, ['shop' => $shop]);

        $this->client->request(
            'POST',
            '/api/backend/shops/' . $shop->getSlug() . '/images',
            ['alt' => 'Test', 'position' => 0],
            ['file' => $file]
        );

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateShopIndexesToEsPropertly(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $this->client->jsonRequest(
            'POST',
            '/api/backend/shops',
            [
                'title' => 'Test',
                'address' => 'Chemin de Montelly 24',
                'zipcode' => 1007,
                'city' => 'Lausanne',
                'state' => 'VD',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        
        $this->esClient->indices()->refresh(['index' => ShopIndex::NAME]);

        $documents = $this->getAllEsShopDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame('Test', $documents[0]['title']);
    }

    public function testUpdateShopUpdateEsDocument(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->esClient->indices()->refresh(['index' => ShopIndex::NAME]);

        $documents = $this->getAllEsShopDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame($shop->getTitle(), $documents[0]['title']);

        $this->client->jsonRequest(
            'PUT',
            '/api/backend/shops/' . $shop->getSlug(),
            [
                'title' => 'Test',
                'address' => 'Chemin de Montelly 24',
                'zipcode' => 1007,
                'city' => 'Lausanne',
                'state' => 'VD',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $this->esClient->indices()->refresh(['index' => ShopIndex::NAME]);

        $documents = $this->getAllEsShopDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame('Test', $documents[0]['title']);
    }

    public function testDeleteShopRemovesEsDocument(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);

        $this->esClient->indices()->refresh(['index' => ShopIndex::NAME]);

        $documents = $this->getAllEsShopDocuments();

        $this->assertCount(1, $documents);
        $this->assertSame($shop->getTitle(), $documents[0]['title']);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/shops/' . $shop->getSlug()
        );

        $this->assertResponseStatusCodeSame(204);

        $this->esClient->indices()->refresh(['index' => ShopIndex::NAME]);

        $documents = $this->getAllEsShopDocuments();

        $this->assertCount(0, $documents);
    }
}