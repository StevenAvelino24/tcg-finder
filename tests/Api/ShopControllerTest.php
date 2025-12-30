<?php

namespace App\Tests\Api;

use App\Factory\GameFactory;
use App\Factory\ImageFactory;
use App\Factory\ShopFactory;
use App\Factory\UserFactory;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Tests\AuthWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

final class ShopControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private ShopRepository $shopRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = static::getContainer()->get(ShopRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
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
                'address' => 'Chemin de la carte 9',
                'zipcode' => 1000,
                'city' => 'Lausanne',
                'state' => 'VD',
                'longitude' => 43.342,
                'latitude' => 43.2344
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
                'address' => 'Chemin de la carte 9',
                'zipcode' => 1000,
                'city' => 'Lausanne',
                'state' => 'VD',
                'longitude' => 43.342,
                'latitude' => 43.2344,
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
                'address' => 'Chemin de la carte 9',
                'zipcode' => 1000,
                'city' => 'Lausanne',
                'state' => 'VD',
                'longitude' => 43.342,
                'latitude' => 43.2344,
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $updatedShop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);
        $this->assertSame('Chemin de la carte 9', $updatedShop->getAddress());
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
}