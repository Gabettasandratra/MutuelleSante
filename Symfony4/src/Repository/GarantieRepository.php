<?php

namespace App\Repository;

use App\Entity\Garantie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Garantie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Garantie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Garantie[]    findAll()
 * @method Garantie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GarantieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Garantie::class);
    }

    // /**
    //  * @return Garantie[] Returns an array of Garantie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Garantie
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
