<?php

namespace App\Repository;

use App\Entity\CotisationPercue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CotisationPercue|null find($id, $lockMode = null, $lockVersion = null)
 * @method CotisationPercue|null findOneBy(array $criteria, array $orderBy = null)
 * @method CotisationPercue[]    findAll()
 * @method CotisationPercue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CotisationPercueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CotisationPercue::class);
    }

    // /**
    //  * @return CotisationPercue[] Returns an array of CotisationPercue objects
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
    public function findOneBySomeField($value): ?CotisationPercue
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
