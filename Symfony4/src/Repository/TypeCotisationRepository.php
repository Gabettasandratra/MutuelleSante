<?php

namespace App\Repository;

use App\Entity\TypeCotisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeCotisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeCotisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeCotisation[]    findAll()
 * @method TypeCotisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeCotisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeCotisation::class);
    }

    // /**
    //  * @return TypeCotisation[] Returns an array of TypeCotisation objects
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
    public function findOneBySomeField($value): ?TypeCotisation
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
