<?php

namespace App\Repository;

use App\Entity\Workshop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Workshop>
 */
    class WorkshopRepository extends ServiceEntityRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, Workshop::class);
        }

        public function findAllWithFiltersAndSorting(
            ?string $searchQuery = null,
            ?string $coachFilter = null,
            ?string $sortBy = null,
            ?string $sortDirection = 'ASC'
        ): array {
            $qb = $this->createQueryBuilder('w');

            if ($searchQuery) {
                $qb->andWhere('w.title LIKE :query OR w.coach LIKE :query')
                    ->setParameter('query', '%' . $searchQuery . '%');
            }

            if ($coachFilter !== null && $coachFilter !== '') {
                $qb->andWhere('w.coach = :coach')
                    ->setParameter('coach', $coachFilter);
            }

            if ($sortBy) {
                $validSortFields = ['startDate', 'duration', 'title', 'coach'];
                if (in_array($sortBy, $validSortFields)) {
                    $qb->orderBy('w.' . $sortBy, $sortDirection);
                }
            }

            return $qb->getQuery()->getResult();
        }
    }


    //    /**
    //     * @return Workshop[] Returns an array of Workshop objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Workshop
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
