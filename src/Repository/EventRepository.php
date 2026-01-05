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
            ->andWhere('event.start_date_time > :now')
            ->setParameter('now', new DateTimeImmutable())
            ->orderBy('event.start_date_time', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByShop(int $shopId): array
    {
        return $this->createQueryBuilder('event')
            ->andWhere('event.shop_id = :shopId')
            ->setParameter('shopId', $shopId)
            ->orderBy('event.start_date_time', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
