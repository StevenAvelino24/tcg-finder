<?php

namespace App\Repository;

use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function findBySearch(
        int $page = 1,
        int $limit = 25,
        ?string $search = null
    ): array {
        $queryBuilder = $this->applySearchFilters($this->createQueryBuilder('shops'), $search);
        $countQueryBuilder = $this->applySearchFilters($this->createQueryBuilder('shops')->select('COUNT(shops.id)'), $search);

        $total = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder
            ->orderBy('shops.title', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'data' => $queryBuilder->getQuery()->getResult(),
            'total' => $total,
        ];
    }

    private function applySearchFilters(
        QueryBuilder $queryBuilder,
        ?string $search
    ): QueryBuilder {
        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('shops.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $queryBuilder;
    }
}
