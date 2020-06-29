<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Prestation;
use App\Entity\Remboursement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Prestation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prestation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prestation[]    findAll()
 * @method Prestation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrestationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prestation::class);
    }

    /**
    * @return Prestation[] Returns an array of Prestation objects
    */

    public function findNotPayed($adherent)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.adherent = :ad')
            ->andWhere('p.isPaye = false')
            ->setParameter('ad', $adherent)
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Prestation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getMontantNotPayed(Adherent $adherent)
    {
        return $this->_em->createQuery('select sum(p.frais),sum(p.rembourse) from App\Entity\Prestation p where p.adherent = :ad and p.isPaye = false')
                            ->setParameter('ad', $adherent)
                            ->getResult();                    
    }

    public function payAll(Remboursement $remboursement)
    {
        return $this->_em->createQuery('update App\Entity\Prestation p set p.isPaye = true, p.remboursement = :rem where p.adherent = :ad and p.isPaye = false ')
                ->setParameter('rem', $remboursement)
                ->setParameter('ad', $remboursement->getAdherent())
                ->getResult();               
    }
}
