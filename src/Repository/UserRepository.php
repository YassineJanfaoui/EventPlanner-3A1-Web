<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getFilteredUsersQueryBuilder(
        ?string $searchQuery = null,
        ?string $roleFilter = null,
        ?string $statusFilter = null,
        ?string $sortBy = null,
        ?string $sortDirection = 'ASC'
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('u');
    
        if ($searchQuery) {
            $qb->andWhere('u.username LIKE :search OR u.email LIKE :search OR u.name LIKE :search')
               ->setParameter('search', '%' . $searchQuery . '%');
        }
    
        if ($roleFilter) {
            $qb->andWhere('u.role = :role')
               ->setParameter('role', strtoupper($roleFilter));
        }
    
        if ($statusFilter) {
            $qb->andWhere('u.status = :status')
               ->setParameter('status', strtolower($statusFilter));
        }
    
        // Define allowed sorting fields and map to entity properties
        $allowedSorts = [
            'userid' => 'u.userid',
            'username' => 'u.username',
            'email' => 'u.email',
            'name' => 'u.name',
            'status' => 'u.status',
            'role' => 'u.role',
        ];
    
        // Fallback to default sort if invalid
        $sortColumn = $allowedSorts[$sortBy] ?? 'u.userid';
        $direction = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';
    
        $qb->orderBy($sortColumn, $direction);
    
        return $qb;
    }
    
}
