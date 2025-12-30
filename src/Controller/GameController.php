<?php

namespace App\Controller;

use App\DTO\Game\CreateGameDTO;
use App\DTO\Game\DetailGameDTO;
use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'games_')]
final class GameController extends AbstractController
{
    #[Route('/games', name: 'list', methods: ['GET'])]
    public function list(GameRepository $gameRepository): JsonResponse
    {
        return $this->json(
            array_map(
                fn (Game $game) => DetailGameDTO::fromEntity($game),
                $gameRepository->findAll()
            ),
            Response::HTTP_OK,
        );
    }

    #[Route('/admin/games', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload()] CreateGameDTO $dto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $game = new Game();
        $game->setName($dto->name);

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->json(
            DetailGameDTO::fromEntity($game),
            Response::HTTP_CREATED
        );
    }

    #[Route('/admin/games/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Game $game,
        #[MapRequestPayload()] CreateGameDTO $dto,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $game->setName($dto->name);

        $entityManager->flush();

        return $this->json(
            DetailGameDTO::fromEntity($game),
            Response::HTTP_OK
        );
    }

    #[Route('/admin/games/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Game $game, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($game);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}