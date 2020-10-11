<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Entity\Remboursement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Remboursement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Remboursement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Remboursement[]    findAll()
 * @method Remboursement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemboursementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Remboursement::class);
    }

    /**
    * @return Remboursement[] Returns an array of Remboursement objects
    */
    public function findRemboursement(Exercice $exercice, Adherent $adherent)
    {
        return $this->_em->createQuery('select r from App\Entity\Remboursement r where r.adherent = :ad and r.date between :dateDebut and :dateFin order by r.date DESC')
                        ->setParameter('ad', $adherent)    
                        ->setParameter('dateDebut', $exercice->getDateDebut())
                        ->setParameter('dateFin', $exercice->getDateFin())                  
                        ->getResult()
        ; 
    
    }

    public function findTotalRemb(Exercice $exercice, Adherent $adherent)
    {
        return $this->_em->createQuery('select sum(r.montant) from App\Entity\Remboursement r where r.adherent = :ad and r.date between :dateDebut and :dateFin order by r.date DESC')
                        ->setParameter('ad', $adherent)    
                        ->setParameter('dateDebut', $exercice->getDateDebut())
                        ->setParameter('dateFin', $exercice->getDateFin())                  
                        ->getSingleScalarResult()
        ; 
    }
}
