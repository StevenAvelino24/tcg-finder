<?php

namespace App\Tests\Api;

use App\Factory\ImageFactory;
use App\Factory\ShopFactory;
use App\Factory\UserFactory;
use App\Repository\ImageRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use App\Tests\AuthWebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ImageControllerTest extends AuthWebTestCase
{
    use ResetDatabase, Factories;

    private ShopRepository $shopRepository;
    private ImageRepository $imageRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = static::getContainer()->get(ShopRepository::class);
        $this->imageRepository = static::getContainer()->get(ImageRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testImageDeleteReturnsErrorIfUserNotCreatorOfShop(): void
    {
        $user = UserFactory::createOne();
        $shop = ShopFactory::createOne(['user' => $user, 'enabled' => false]);
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $image = ImageFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/images/' . $image->getId()
        );

        $this->assertResponseStatusCodeSame(403);
    }

    public function testImageDeleteReturnsSuccessIfUserIsShopCreator(): void
    {
        $this->authenticate('user@tcg.ch', 'Password1234', ['ROLE_USER']);
        $user = $this->userRepository->findOneBy(['email' => 'user@tcg.ch']);
        $shop = ShopFactory::createOne(['user' => $user]);
        $image = ImageFactory::createOne(['shop' => $shop]);

        $this->client->jsonRequest(
            'DELETE',
            '/api/backend/images/' . $image->getId()
        );

        $this->assertResponseStatusCodeSame(204);

        $updatedImage = $this->imageRepository->findOneBy(['id' => $image->getId()]);
        $this->assertNull($updatedImage);
        $updatedShop = $this->shopRepository->findOneBy(['slug' => $shop->getSlug()]);
        $this->assertCount(0, $updatedShop->getImages());
    }
}