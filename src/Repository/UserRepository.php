<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findSearchAndPaginated(
        int $page = 1,
        int $limit = 25,
        ?string $search = null,
        bool $verified = true
    ): array {
        $queryBuilder = $this->applySearchFilters($this->createQueryBuilder('users'), $search, $verified);
        $countQueryBuilder = $this->applySearchFilters($this->createQueryBuilder('users')->select('COUNT(users.id)'), $search, $verified);

        $total = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        $queryBuilder
            ->orderBy('users.email', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'data' => $queryBuilder->getQuery()->getResult(),
            'total' => $total,
        ];
    }

    private function applySearchFilters(
        QueryBuilder $queryBuilder,
        ?string $search,
        bool $verified
    ): QueryBuilder {
        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('users.email LIKE :search OR users.firstName LIKE :search OR users.lastName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder
            ->andWhere('users.isVerified = :verified')
            ->setParameter('verified', $verified);

        return $queryBuilder;
    }
}
