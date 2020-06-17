<?php

namespace App\Repository;

use App\Entity\TailleFamille;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TailleFamille|null find($id, $lockMode = null, $lockVersion = null)
 * @method TailleFamille|null findOneBy(array $criteria, array $orderBy = null)
 * @method TailleFamille[]    findAll()
 * @method TailleFamille[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TailleFamilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TailleFamille::class);
    }

    // /**
    //  * @return TailleFamille[] Returns an array of TailleFamille objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TailleFamille
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
