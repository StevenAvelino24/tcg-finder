<?php

namespace App\Controller;

use App\DTO\Image\DetailImageDTO;
use App\DTO\Shop\CreateShopDTO;
use App\DTO\Shop\DetailShopDTO;
use App\Entity\Shop;
use App\Repository\ShopRepository;
use App\Service\Geocoding\NominatimService;
use App\Service\Shop\ShopServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\Shop\UploadImageShopDTO;
use App\Service\Image\ImageServiceInterface;

#[Route('/api', name: 'shops_')]
final class ShopController extends AbstractController
{
    public function __construct(
        protected readonly ShopRepository $shopRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ShopServiceInterface $shopService,
        protected readonly NominatimService $geocodingService,
        protected readonly ImageServiceInterface $imageService,
        protected readonly ValidatorInterface $validator
    ) {}

    #[Route('/admin/shops', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 25)));
        $search = $request->query->getString('search', '');

        $shops = $this->shopRepository->findBySearch(
            $page,
            $limit,
            $search
        );

        return $this->json(
            [
                'shops' => array_map(
                    fn (Shop $shop) => DetailShopDTO::fromEntity($shop),
                    $shops['data']
                ),
                'total' => $shops['total']
            ], 
            Response::HTTP_OK
        );
    }

    #[Route('/admin/shops/{id}/enable', name: 'enable', methods: ['PUT'])]
    public function enable(
        #[MapEntity(mapping: ['id' => 'id'])] Shop $shop
    ): JsonResponse {
        $shop->setEnabled(true);

        $this->entityManager->flush();

        return $this->json(null,Response::HTTP_OK);
    }

    #[Route('/admin/shops/{id}/disable', name: 'disable', methods: ['PUT'])]
    public function disable(
        #[MapEntity(mapping: ['id' => 'id'])] Shop $shop
    ): JsonResponse {
        $shop->setEnabled(false);

        $this->entityManager->flush();

        return $this->json(null,Response::HTTP_OK);
    }

    #[IsGranted('SHOP_BACKEND_SHOW', subject: 'shop')]
    #[Route('/backend/shops/{slug}', name: 'backend_show', methods: ['GET'])]
    public function backendShow(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop
    ): JsonResponse {
        return $this->json(
            DetailShopDTO::fromEntity($shop),
            Response::HTTP_OK
        );
    }

    #[Route('/shops/{slug}', name: 'show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop
    ): JsonResponse {
        if (!$shop->getEnabled()) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }
        
        return $this->json(
            DetailShopDTO::fromEntity($shop),
            Response::HTTP_OK
        );
    }

    #[IsGranted('SHOP_CREATE', subject: null)]
    #[Route('/backend/shops', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateShopDTO $dto,
    ): JsonResponse {
        $shop = $this->shopService->createFromDTO($dto, $this->getUser());
        $geoInfo = $this->geocodingService->getLatAndLonFromAddress($shop->getAddress(), $shop->getZipcode(), $shop->getCity());
        if (empty($geoInfo)) {
            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        } 

        $shop->setLatitude($geoInfo['lat']);
        $shop->setLongitude($geoInfo['lon']);

        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        return $this->json(
            DetailShopDTO::fromEntity($shop),
            Response::HTTP_CREATED
        );
    }

    #[IsGranted('SHOP_EDIT', subject: 'shop')]
    #[Route('/backend/shops/{slug}', name: 'update', methods: ['PUT'])]
    public function update(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
        #[MapRequestPayload] CreateShopDTO $dto
    ): JsonResponse {
        $this->shopService->updateFromDTO($dto, $shop);
        $geoInfo = $this->geocodingService->getLatAndLonFromAddress($shop->getAddress(), $shop->getZipcode(), $shop->getCity());
        if (empty($geoInfo)) {
            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shop->setLatitude($geoInfo['lat']);
        $shop->setLongitude($geoInfo['lon']);

        $this->entityManager->flush();

        return $this->json(
            DetailShopDTO::fromEntity($shop),
            Response::HTTP_OK
        );
    }

    #[IsGranted('SHOP_EDIT', subject: 'shop')]
    #[Route('/backend/shops/{slug}/images', methods: ['POST'])]
    public function uploadImage(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
        Request $request,
    ): JsonResponse {
        $dto = new UploadImageShopDTO();
        $dto->file = $request->files->get('file');
        $dto->alt = $request->request->get('alt');
        $dto->position = $request->request->get('position');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $image = $this->imageService->createFromDTO($dto, $shop);

        if (!$image) {
            return $this->json(null,Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $this->json(
            DetailImageDTO::fromEntity($image),
            Response::HTTP_CREATED
        );
    }

    #[IsGranted('SHOP_DELETE', subject: 'shop')]
    #[Route('/backend/shops/{slug}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
    ): JsonResponse {
        $this->entityManager->remove($shop);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}