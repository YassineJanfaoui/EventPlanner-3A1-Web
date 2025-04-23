<?php

namespace App\Repository;

use App\Entity\Equipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipment>
 */
class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }
    public function findAllWithFiltersAndSorting(
        ?string $searchQuery = null,
        ?string $CategoryFilter = null,
        ?string $sortBy = null,
        ?string $sortDirection = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('b');

        if ($searchQuery) {
            $qb->andWhere('b.name LIKE :query 
                OR b.category LIKE :query 
                OR b.price LIKE :query')
                ->setParameter('query', '%' . $searchQuery . '%');
        }

        if ($CategoryFilter !== null && $CategoryFilter !== '') {
            $qb->andWhere('b.category = :Category')
                ->setParameter('Category', $CategoryFilter);
        }

        if ($sortBy) {
            $validSortFields = ['price', 'state', 'quantity'];
            if (in_array($sortBy, $validSortFields)) {
                $qb->orderBy('b.' . $sortBy, $sortDirection);
            }
        }

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Equipment[] Returns an array of Equipment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Equipment
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
