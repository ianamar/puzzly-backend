<?php

namespace App\Repository;

use App\Entity\UserPuzzle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPuzzle>
 */
class UserPuzzleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPuzzle::class);
    }


public function findOneByUserAndPuzzle($user, $puzzle): ?UserPuzzle
{
    return $this->createQueryBuilder('up')
        ->andWhere('up.user = :user')
        ->andWhere('up.puzzle = :puzzle')
        ->setParameter('user', $user)
        ->setParameter('puzzle', $puzzle)
        ->getQuery()
        ->getOneOrNullResult();
}
}
