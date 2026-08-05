<?php

namespace App\Repository;

use App\Entity\Event;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
