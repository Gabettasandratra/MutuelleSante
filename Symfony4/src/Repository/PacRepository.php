<?php

namespace App\Repository;

use App\Entity\Pac;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pac|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pac|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pac[]    findAll()
 * @method Pac[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pac::class);
    }

    // /**
    //  * @return Pac[] Returns an array of Pac objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pac
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
