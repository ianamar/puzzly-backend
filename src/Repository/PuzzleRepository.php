<?php

namespace App\Repository;

use App\Entity\Puzzle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Puzzle>
 *
 * @method Puzzle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Puzzle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Puzzle[]    findAll()
 * @method Puzzle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PuzzleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Puzzle::class);
    }

    /**
     * Buscar por nombre (case-insensitive, LIKE %q%)
     * @return Puzzle[]
     */
    public function findByNameLike(string $q, int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE :q')
            ->setParameter('q', '%' . mb_strtolower($q) . '%')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

}
