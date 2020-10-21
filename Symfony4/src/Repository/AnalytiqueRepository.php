<?php

namespace App\Repository;

use App\Entity\Analytique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Analytique|null find($id, $lockMode = null, $lockVersion = null)
 * @method Analytique|null findOneBy(array $criteria, array $orderBy = null)
 * @method Analytique[]    findAll()
 * @method Analytique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalytiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analytique::class);
    }

    public function findAnalytics()
    {
        return $this->_em->createQuery('select t.id, t.code, t.libelle from App\Entity\Analytique t')
                    ->getArrayResult(); 
    }
}
