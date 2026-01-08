<?php

namespace App\Controller;

use App\DTO\Event\CreateEventDTO;
use App\DTO\Event\DetailEventDTO;
use App\DTO\Participant\CreateParticipantDTO;
use App\DTO\Participant\DetailParticipantDTO;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Service\Event\EventServiceInterface;
use App\Service\Participant\ParticipantServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'events_')]
final class EventController extends AbstractController
{
    #[Route('/admin/events', name: 'list', methods: ['GET'])]
    public function list(EventRepository $eventRepository): JsonResponse
    {
        return $this->json(
            array_map(
                fn (Event $event) => DetailEventDTO::fromEntity($event),
                $eventRepository->findAll()
            ),
            Response::HTTP_OK
        );
    }

    #[Route('/backend/events', name: 'shop_list', methods: ['GET'])]
    public function shopList(EventRepository $eventRepository): JsonResponse
    {
        /** @var User */
        $user = $this->getUser();
        $shop = $user->getShop();

        if (!$shop || !$shop->getEnabled()) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        return $this->json(
            array_map(
                fn (Event $event) => DetailEventDTO::fromEntity($event),
                $eventRepository->findByShop($shop->getId())
            ),
            Response::HTTP_OK
        );
    }

    #[Route('/events/{slug}', name: 'show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event
    ): JsonResponse {
        $shop = $event->getShop();

        if (!$shop->getEnabled()) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            DetailEventDTO::fromEntity($event),
            Response::HTTP_OK
        );
    }

    #[Route('/backend/events', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload()] CreateEventDTO $dto,
        EventServiceInterface $eventService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User */
        $user = $this->getUser();
        $shop = $user->getShop();
        
        if (!$shop || !$shop->getEnabled()) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $event = $eventService->createFromDTO($dto, $user->getShop());

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->json(
            DetailEventDTO::fromEntity($event),
            Response::HTTP_CREATED
        );
    }

    #[IsGranted('EVENT_EDIT', subject: 'event')]
    #[Route('/backend/events/{slug}', name: 'update', methods: ['PUT'])]
    public function update(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event,
        #[MapRequestPayload] CreateEventDTO $dto,
        EventServiceInterface $eventService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $eventService->updateFromDTO($dto, $event);

        $entityManager->flush();

        return $this->json(
            DetailEventDTO::fromEntity($event),
            Response::HTTP_OK
        );
    }

    #[IsGranted('EVENT_DELETE', subject: 'event')]
    #[Route('/backend/events/{slug}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/events/{slug}/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event,
        #[MapRequestPayload] CreateParticipantDTO $dto,
        EntityManagerInterface $entityManager,
        ParticipantServiceInterface $participantService
    ): JsonResponse {
        $shop = $event->getShop();

        if (!$shop->getEnabled()) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $participant = $participantService->createFromDTO($dto, $event);

        $entityManager->persist($participant);
        $entityManager->flush();

        return $this->json(DetailParticipantDTO::fromEntity($participant), Response::HTTP_CREATED);
    }

    #[Route('/events/unregister', name: 'unregister', methods: ['DELETE'])]
    public function unregister(
        Request $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository
    ): JsonResponse {
        $token = $request->query->get('token');

        if (!$token) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $participant = $participantRepository->findOneBy(['unregisterToken' => $token]);

        if (!$participant) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($participant);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}