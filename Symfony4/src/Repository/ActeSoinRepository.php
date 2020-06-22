<?php

namespace App\Repository;

use App\Entity\ActeSoin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActeSoin|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActeSoin|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActeSoin[]    findAll()
 * @method ActeSoin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActeSoinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActeSoin::class);
    }

    // /**
    //  * @return ActeSoin[] Returns an array of ActeSoin objects
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
    public function findOneBySomeField($value): ?ActeSoin
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
