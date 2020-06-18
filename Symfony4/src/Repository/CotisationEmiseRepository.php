<?php

namespace App\Repository;

use App\Entity\CotisationEmise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CotisationEmise|null find($id, $lockMode = null, $lockVersion = null)
 * @method CotisationEmise|null findOneBy(array $criteria, array $orderBy = null)
 * @method CotisationEmise[]    findAll()
 * @method CotisationEmise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CotisationEmiseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CotisationEmise::class);
    }

    // /**
    //  * @return CotisationEmise[] Returns an array of CotisationEmise objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CotisationEmise
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
