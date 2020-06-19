<?php

namespace App\Repository;

use App\Entity\ArriereAvance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArriereAvance|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArriereAvance|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArriereAvance[]    findAll()
 * @method ArriereAvance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArriereAvanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArriereAvance::class);
    }

    // /**
    //  * @return ArriereAvance[] Returns an array of ArriereAvance objects
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
    public function findOneBySomeField($value): ?ArriereAvance
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
