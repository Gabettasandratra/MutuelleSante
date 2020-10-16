<?php

namespace App\Repository;

use App\Entity\Analytique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Analytique|null find($id, $lockMode = null, $lockVersion = null)
 * @method Analytique|null findOneBy(array $criteria, array $orderBy = null)
 * @method Analytique[]    findAll()
 * @method Analytique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalytiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analytique::class);
    }

    // /**
    //  * @return Analytique[] Returns an array of Analytique objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Analytique
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
