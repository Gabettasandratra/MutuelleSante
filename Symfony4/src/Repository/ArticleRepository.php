<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
    * @return Article[] Returns an array of Article objects
    */
    public function findCotisations()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :cat')
            ->setParameter('cat', 'Cotisation')
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Article[] Returns an array of Article objects
    */
    public function findRemboursements()
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.categorie = :cat')
            ->setParameter('cat', 'Remboursement')
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Article
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
