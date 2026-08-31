<?php

namespace App\Repository;

use App\Entity\Event;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllFuture(): array
    {
        return $this->createQueryBuilder('event')
            ->andWhere('event.startDateTime > :now')
            ->setParameter('now', new DateTimeImmutable())
            ->orderBy('event.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByShop(int $shopId): array
    {
        return $this->createQueryBuilder('event')
            ->andWhere('event.shop = :shopId')
            ->setParameter('shopId', $shopId)
            ->orderBy('event.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySearch(
        int $page = 1,
        int $limit = 25,
        ?string $search = null,
        ?int $shopId = null,
        ?int $gameId = null
    ): array {
        $queryBuilder = $this->applySearchFilters($this->createQueryBuilder('events'), $search, $shopId, $gameId);
        $countQueryBuilder = $this->applySearchFilters($this->createQueryBuilder('events')->select('COUNT(events.id)'), $search, $shopId, $gameId);

        $queryBuilder
            ->orderBy('events.startDateTime', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $total = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        return [
            'data' => $queryBuilder->getQuery()->getResult(),
            'total' => $total,
        ];
    }

    private function applySearchFilters(
        QueryBuilder $queryBuilder,
        ?string $search,
        ?int $shopId,
        ?int $gameId
    ): QueryBuilder {
        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('events.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($shopId) {
            $queryBuilder
                ->andWhere('events.shop = :shopId')
                ->setParameter('shopId', $shopId);
        }

        if ($gameId) {
            $queryBuilder
                ->andWhere('events.game = :gameId')
                ->setParameter('gameId', $gameId);
        }

        return $queryBuilder;
    }
}
