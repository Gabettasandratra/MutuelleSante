<?php

namespace App\Repository;

use App\Entity\CompteCotisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompteCotisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompteCotisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompteCotisation[]    findAll()
 * @method CompteCotisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteCotisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompteCotisation::class);
    }

    // /**
    //  * @return CompteCotisation[] Returns an array of CompteCotisation objects
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



}
