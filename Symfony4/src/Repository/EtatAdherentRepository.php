<?php

namespace App\Repository;

use App\Entity\EtatAdherent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EtatAdherent|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatAdherent|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatAdherent[]    findAll()
 * @method EtatAdherent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtatAdherentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatAdherent::class);
    }

    // /**
    //  * @return EtatAdherent[] Returns an array of EtatAdherent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EtatAdherent
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
