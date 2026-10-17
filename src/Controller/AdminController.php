<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]

final class AdminController extends AbstractController
{
    #[Route(
        '/users',
        name: 'users_list',
        methods: ['GET']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function usersList(Request $request, UserRepository $userRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 25)));
        $search = $request->query->getString('search', '');
        $verified = $request->query->getBoolean('verified', true);

        $users = $userRepository->findSearchAndPaginated(
            $page,
            $limit,
            $search === '' ? null : $search,
            $verified
        );

        return $this->render(
            'admin/users-list.html.twig',
            [
                'users' => $users['data'],
                'total' => $users['total'],
                'page' => $page,
                'limit' => $limit,
                'search' => $search,
                'verified' => $verified,
            ],
        );
    }

    #[Route(
        '/users/delete/{id}',
        name: 'users_delete',
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function userDelete(
        Request $request,
        #[MapEntity(mapping: ['id' => 'id'])] User $user,
        EntityManagerInterface $entityManager
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete-user-' . $user->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'admin.users.deleted');

        return $this->redirectToRoute('admin_users_list');
    }
}