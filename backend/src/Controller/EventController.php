<?php

namespace App\Controller;

use App\DTO\Event\CreateEventDTO;
use App\DTO\Event\DetailEventDTO;
use App\DTO\Participant\CreateParticipantDTO;
use App\DTO\Participant\DetailParticipantDTO;
use App\Entity\Event;
use App\Entity\Shop;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Service\Event\EventServiceInterface;
use App\Service\Participant\ParticipantServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
    public function __construct(
        protected readonly EventRepository $eventRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly EventServiceInterface $eventService,
        protected readonly ParticipantServiceInterface $participantService,
        protected readonly ParticipantRepository $participantRepository
    ) {}

    #[Route('/admin/events', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 25)));
        $search = $request->query->getString('search', '');
        $shopId = $request->query->getInt('shop_id');
        $gameId = $request->query->getInt('game_id');

        $events = $this->eventRepository->findBySearch(
            $page,
            $limit,
            $search,
            $shopId,
            $gameId
        );

        return $this->json(
            [
                'events' => array_map(
                    fn (Event $event) => DetailEventDTO::fromEntity($event),
                    $events['data']
                ),
                'total' => $events['total']
            ],   
            Response::HTTP_OK
        );
    }

    #[Route('/backend/shops/{slug}/events', name: 'shop_list', methods: ['GET'])]
    public function shopList(
        #[MapEntity(mapping: ['slug' => 'slug'])] Shop $shop,
    ): JsonResponse {
        if (!$shop || !$shop->getEnabled()) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        return $this->json(
            array_map(
                fn (Event $event) => DetailEventDTO::fromEntity($event),
                $this->eventRepository->findByShop($shop->getId())
            ),
            Response::HTTP_OK
        );
    }

    #[Route('/events/{slug}', name: 'show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event,
    ): JsonResponse {
        if (!$event->getShop()->getEnabled()) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        return $this->json(DetailEventDTO::fromEntity($event), Response::HTTP_OK);
    }

    #[Route('/backend/events', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload()] CreateEventDTO $dto
    ): JsonResponse {
        $event = $this->eventService->createFromDTO($dto);

        if (!$event) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

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
    ): JsonResponse {
        $this->eventService->updateFromDTO($dto, $event);

        $this->entityManager->flush();

        return $this->json(
            DetailEventDTO::fromEntity($event),
            Response::HTTP_OK
        );
    }

    #[IsGranted('EVENT_DELETE', subject: 'event')]
    #[Route('/backend/events/{slug}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event,
    ): JsonResponse {
        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/events/{slug}/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapEntity(mapping: ['slug' => 'slug'])] Event $event,
        #[MapRequestPayload] CreateParticipantDTO $dto,
    ): JsonResponse {
        $this->entityManager->beginTransaction();

        try {
            if ($event->getNumberParticipants() <= $event->getParticipants()->count()) {
                return $this->json(null, Response::HTTP_FORBIDDEN);
            }
            
            $shop = $event->getShop();

            if (!$shop->getEnabled()) {
                return $this->json(null, Response::HTTP_FORBIDDEN);
            }

            $participant = $this->participantService->createFromDTO($dto, $event);

            $this->entityManager->persist($participant);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();

            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        return $this->json(DetailParticipantDTO::fromEntity($participant), Response::HTTP_CREATED);
    }

    #[Route('/events/unregister', name: 'unregister', methods: ['DELETE'])]
    public function unregister(Request $request): JsonResponse {
        $token = $request->query->get('token');

        if (!$token) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $participant = $this->participantRepository->findOneBy(['unregisterToken' => $token]);

        if (!$participant) {
            return $this->json(null, Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($participant);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}