<?php

namespace App\Repository;

use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Shop>
 */
class ShopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shop::class);
    }

    public function findByEnabled(bool $enabled = true): array
    {
        return $this->createQueryBuilder('shop')
            ->andWhere('shop.enabled = :value')
            ->setParameter('value', $enabled)
            ->getQuery()
            ->getResult();
    }

    public function findOneEnabledBySlug(string $slug): ?Shop
    {
        return $this->createQueryBuilder('shop')
            ->andWhere('shop.enabled = true')
            ->andWhere('shop.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
