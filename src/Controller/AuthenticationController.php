<?php

namespace App\Controller;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\DetailUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Entity\User;
use App\Service\User\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'auth_')]
final class AuthenticationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload()] CreateUserDTO $dto,
        UserServiceInterface $userService,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $user = $userService->createFromDTO($dto);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(
            DetailUserDTO::fromEntity($user),
            Response::HTTP_CREATED
        );
    }

    #[Route('/backend/user', name: 'update', methods: ['PUT'])]
    public function update(
        #[MapRequestPayload()] UpdateUserDTO $dto,
        UserServiceInterface $userService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User **/
        $user = $this->getUser();
        $userService->updateFromDTO($dto, $user);

        $entityManager->flush();

        return $this->json(
            DetailUserDTO::fromEntity($user),
            Response::HTTP_OK
        );
    }

    #[Route('/backend/user', name: 'delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($this->getUser());
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/backend/user', name: 'show', methods: ['GET'])]
    public function getAuthenticatedUser(): JsonResponse
    {
        /** @var User **/
        $user = $this->getUser();

        return $this->json(
            DetailUserDTO::fromEntity($user),
            Response::HTTP_OK
        );
    }
}