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
use Symfony\Contracts\Translation\TranslatorInterface;
use App\DTO\Shop\UploadImageShopDTO;
use App\Service\Image\ImageServiceInterface;

#[Route('/api', name: 'shops_')]
final class ShopController extends AbstractController
{


    #[Route('/admin/shops', name: 'list', methods: ['GET'])]
    public function list(ShopRepository $shopRepository): JsonResponse
    {
        return $this->json(
            array_map(
                fn (Shop $shop) => DetailShopDTO::fromEntity($shop),
                $shopRepository->findAll()
            ),
            Response::HTTP_OK
        );
    }

    #[Route('/admin/shops/{slug}/enable', name: 'enable', methods: ['PUT'])]
    public function enable(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): JsonResponse {
        $shop->setEnabled(true);

        $entityManager->flush();

        return $this->json(
            ['message' => $translator->trans('shop.enabled.success')],
            Response::HTTP_OK
        );
    }

    #[Route('/admin/shops/{slug}/disable', name: 'disable', methods: ['PUT'])]
    public function disable(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): JsonResponse {
        $shop->setEnabled(false);

        $entityManager->flush();

        return $this->json(
            ['message' => $translator->trans('shop.disabled.success')],
            Response::HTTP_OK
        );
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
        string $slug,
        ShopRepository $shopRepository,
        TranslatorInterface $translator
    ): JsonResponse {
        $shop = $shopRepository->findOneEnabledBySlug($slug);

        if (!$shop) {
            return $this->json(
                ['message' => $translator->trans('shop.not_found')],
                Response::HTTP_NOT_FOUND
            );
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
        ShopServiceInterface $shopService,
        NominatimService $geocodingService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $shop = $shopService->createFromDTO($dto, $this->getUser());
        $geoInfo = $geocodingService->getLatAndLonFromAddress($shop->getAddress(), $shop->getZipcode(), $shop->getCity());
        if (empty($geoInfo)) {
            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        } 

        $shop->setLatitude($geoInfo['lat']);
        $shop->setLongitude($geoInfo['lon']);

        $entityManager->persist($shop);
        $entityManager->flush();

        return $this->json(
            DetailShopDTO::fromEntity($shop),
            Response::HTTP_CREATED
        );
    }

    #[IsGranted('SHOP_EDIT', subject: 'shop')]
    #[Route('/backend/shops/{slug}', name: 'update', methods: ['PUT'])]
    public function update(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
        #[MapRequestPayload] CreateShopDTO $dto,
        ShopServiceInterface $shopService,
        NominatimService $geocodingService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $shopService->updateFromDTO($dto, $shop);
        $geoInfo = $geocodingService->getLatAndLonFromAddress($shop->getAddress(), $shop->getZipcode(), $shop->getCity());
        if (empty($geoInfo)) {
            return $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shop->setLatitude($geoInfo['lat']);
        $shop->setLongitude($geoInfo['lon']);

        $entityManager->flush();

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
        ImageServiceInterface $imageService,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): JsonResponse {
        $dto = new UploadImageShopDTO();
        $dto->file = $request->files->get('file');
        $dto->alt = $request->request->get('alt');
        $dto->position = $request->request->get('position');

        $errors = $validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $image = $imageService->createFromDTO($dto, $shop);

        if (!$image) {
            return $this->json(
                $translator->trans('shop.images.upload.failed'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $entityManager->persist($image);
        $entityManager->flush();

        return $this->json(
            DetailImageDTO::fromEntity($image),
            Response::HTTP_CREATED
        );
    }

    #[IsGranted('SHOP_DELETE', subject: 'shop')]
    #[Route('/backend/shops/{slug}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($shop);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}