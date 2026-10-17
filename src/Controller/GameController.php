<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\Game\CreateGameFormType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'games_')]
final class GameController extends AbstractController
{
    #[Route('/games', name: 'list', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(
        Request $request,
        GameRepository $gameRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $game = new Game();
        $form = $this->createForm(CreateGameFormType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game->setName($form->get('name')->getData());

            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'games.added');

            $this->redirectToRoute('games_list');
        }

        return $this->render('admin/games-list.html.twig', [
            'games' => $gameRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/games/{id}', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] Game $game,
        EntityManagerInterface $entityManager,
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete-game-' . $game->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($game);
        $entityManager->flush();

        $this->addFlash('success', 'admin.games.deleted');

        return $this->redirectToRoute('games_list');
    }
}