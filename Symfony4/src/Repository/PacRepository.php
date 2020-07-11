<?php

namespace App\Repository;

use App\Entity\Pac;
use App\Entity\Adherent;
use App\Entity\Exercice;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Pac|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pac|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pac[]    findAll()
 * @method Pac[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pac::class);
    }

    public function generateCode(Adherent $adherent)
    {
        // get the last id
        $lastCode = (int) $this->_em->createQuery('select max(p.codeMutuelle) from App\Entity\Pac p')
                            ->getSingleScalarResult();
        return ++$lastCode;
    }

    public function findPacRetirer()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isSortie = true')
            ->orderBy('p.codeMutuelle', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
