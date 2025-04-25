<?php

namespace App\Repository;

use App\Entity\Partner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partner>
 */
class PartnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partner::class);
    }

    public function findAllWithFiltersAndSorting(
        ?string $searchQuery = null,
        ?string $categoryFilter = null,
    ): array {
        $qb = $this->createQueryBuilder('w');

        if ($searchQuery) {
            $qb->andWhere('w.name LIKE :query OR w.name LIKE :query')
                ->setParameter('query', '%' . $searchQuery . '%');
        }

        if ($categoryFilter !== null && $categoryFilter !== '') {
            $qb->andWhere('w.category = :category')
                ->setParameter('category', $categoryFilter);
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Partner[] Returns an array of Partner objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Partner
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
