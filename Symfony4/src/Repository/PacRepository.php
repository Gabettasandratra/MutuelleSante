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

    public function findByDateEnter($periode, Adherent $adherent = null)
    {
        $dql = 'select p from App\Entity\Pac p where p.dateEntrer between :deb and :fin';
        if ($adherent) { 
            $dql = $dql. ' and p.adherent = :ad';
            return $this->_em->createQuery($dql)
                        ->setParameter('deb', $periode['debut'])
                        ->setParameter('fin', $periode['fin'])
                        ->setParameter('ad', $adherent)
                        ->getResult();
        }
        return $this->_em->createQuery($dql)
                        ->setParameter('deb', $periode['debut'])
                        ->setParameter('fin', $periode['fin'])
                        ->getResult();
    }
}
