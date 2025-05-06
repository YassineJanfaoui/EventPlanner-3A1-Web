<?php

namespace App\Repository;

use App\Entity\Venue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Venue>
 */
class VenueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venue::class);
    }

    public function findSortedVenues($sortBy, $sortDir)
    {
        return $this->createQueryBuilder('v')
                    ->orderBy('v.' . $sortBy, $sortDir)
                    ->getQuery()
                    ->getResult();
    }

    public function getFilteredQueryBuilder(?string $location, ?string $availability, ?string $parking, ?string $venueName, ?string $sortBy, ?string $sortDirection)
    {
        $qb = $this->createQueryBuilder('v');

        if ($location) {
            $qb->andWhere('v.Location LIKE :location')
            ->setParameter('location', '%'.$location.'%');
        }

        if ($availability) {
            $qb->andWhere('v.Availability LIKE :availability')
            ->setParameter('availability', '%'.$availability.'%');
        }

        if ($parking) {
            $qb->andWhere('v.Parking = :parking')
            ->setParameter('parking', $parking);
        }

        if ($venueName) {
            $qb->andWhere('v.VenueName LIKE :venueName')
            ->setParameter('venueName', '%'.$venueName.'%');
        }
        // Map sort fields to their actual database column names
        $fieldMapping = [
            'Cost' => 'Cost',        // Matches the entity property
            'NbrPlaces' => 'NbrPlaces', // Matches the entity property
            'VenueId' => 'VenueId',
            'Location' => 'Location',
            'VenueName' => 'VenueName',
            'Availability' => 'Availability',
            'Parking' => 'Parking'
        ];

        if (isset($fieldMapping[$sortBy])) {
            $qb->orderBy('v.' . $fieldMapping[$sortBy], strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $qb->orderBy('v.VenueId', 'ASC');
        }

        return $qb;
    }

    public function findBestQualityPriceVenue(): ?Venue
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.NbrPlaces > 0')
            ->addSelect('(v.Cost / v.NbrPlaces) AS HIDDEN qualitePrix')
            ->orderBy('qualitePrix', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRecommendations(Venue $venue): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.VenueId != :currentId')
            ->andWhere('v.Location = :location OR ABS(v.NbrPlaces - :nbrPlaces) <= 1000 OR ABS(v.Cost - :cost) <= 1000')
            ->setParameter('currentId', $venue->getVenueId())
            ->setParameter('location', $venue->getLocation())
            ->setParameter('nbrPlaces', $venue->getNbrPlaces())
            ->setParameter('cost', $venue->getCost())
            ->setMaxResults(4)
            ->getQuery()
            ->getResult();
    }


public function findFilteredVenues($filters = [])
{
    $qb = $this->createQueryBuilder('v');

    if (!empty($filters['venue_name'])) {
        $qb->andWhere('v.VenueName LIKE :venueName')
           ->setParameter('venueName', '%' . $filters['venue_name'] . '%');
    }

    if (!empty($filters['location'])) {
        $qb->andWhere('v.Location LIKE :location')
           ->setParameter('location', '%' . $filters['location'] . '%');
    }

    if (!empty($filters['availability'])) {
        $qb->andWhere('v.Availability = :availability')
           ->setParameter('availability', $filters['availability']);
    }

    if (!empty($filters['parking'])) {
        $qb->andWhere('v.Parking = :parking')
           ->setParameter('parking', $filters['parking']);
    }

    return $qb->getQuery()->getResult();
}




//    /**
//     * @return Venue[] Returns an array of Venue objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Venue
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
