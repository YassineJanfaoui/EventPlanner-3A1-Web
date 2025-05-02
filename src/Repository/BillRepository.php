<?php

namespace App\Repository;

use App\Entity\Bill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;


/**
 * @extends ServiceEntityRepository<Bill>
 */
class BillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bill::class);
    }
    public function findAllWithFiltersAndSorting(
        ?string $searchQuery = null,
        ?int $archivedFilter = 0,
        ?string $sortBy = null,
        ?string $sortDirection = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('b');

        if ($searchQuery) {
            $qb->andWhere('b.Description LIKE :query 
                OR b.Amount LIKE :query 
                OR b.PaymentStatus LIKE :query')
                ->setParameter('query', '%' . $searchQuery . '%');
        }

        if ($archivedFilter !== null && $archivedFilter !== 'all') {
            $qb->andWhere('b.Archived = :archived')
                ->setParameter('archived', $archivedFilter);
        }

        if ($sortBy) {
            $validSortFields = ['DueDate', 'PaymentStatus', 'Amount'];
            if (in_array($sortBy, $validSortFields)) {
                $qb->orderBy('b.' . $sortBy, $sortDirection);
            }
        }

        return $qb->getQuery()->getResult();
    }
    public function createQueryBuilderWithFiltersAndSorting(
        ?string $searchQuery,
        ?int $archivedFilter,
        ?string $sortBy,
        ?string $sortDirection
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('b');

        if ($searchQuery) {
            $qb->andWhere('b.Description LIKE :searchQuery OR b.PaymentStatus LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $searchQuery . '%');
        }

        $qb->andWhere('b.Archived = :archived')
            ->setParameter('archived', $archivedFilter);

        if ($sortBy && $sortDirection) {
            $qb->orderBy('b.' . $sortBy, $sortDirection);
        }

        return $qb;
    }
    public function findBillsDueWithin(int $days): array
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->where('b.DueDate BETWEEN :now AND :future')
            ->andWhere('b.PaymentStatus = :pending')
            ->setParameter('now', new \DateTime())
            ->setParameter('future', (new \DateTime())->modify("+$days days"))
            ->setParameter('pending', 'pending')
            ->orderBy('b.DueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Bill[] Returns an array of Bill objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Bill
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
