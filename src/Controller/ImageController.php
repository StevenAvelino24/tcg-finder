<?php

namespace App\Controller;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/backend/images', name: 'image_')]
final class ImageController extends AbstractController
{
    #[IsGranted('IMAGE_DELETE', subject: 'image')]
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Image $image, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($image);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}