<?php

namespace App\Repository;

use App\Entity\Event;
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

    //    /**
    //     * @return Event[] Returns an array of Event objects
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

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findWithVenueByEvent(Event $event): array
{
    return $this->createQueryBuilder('r')
        ->leftJoin('r.venue', 'v')->addSelect('v')
        ->andWhere('r.event = :event')
        ->setParameter('event', $event)
        ->getQuery()
        ->getResult();
}
public function searchByKeyword(?string $keyword): array
{
    $qb = $this->createQueryBuilder('e');

    if ($keyword) {
        $qb->andWhere('e.name LIKE :keyword OR e.description LIKE :keyword')
           ->setParameter('keyword', '%' . $keyword . '%');
    }

    return $qb->getQuery()->getResult();
}
public function searchByTerm(string $term)
{
    return $this->createQueryBuilder('e')
        ->where('e.name LIKE :term')
        ->orWhere('e.lieu LIKE :term')
        ->orWhere('e.description LIKE :term')
        ->orWhere('e.startDate LIKE :term')
        ->orWhere('e.endDate LIKE :term')
        ->orWhere('e.fee LIKE :term')
        ->orWhere('e.maxParticipants LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}
}
