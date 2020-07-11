<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Exercice;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Adherent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adherent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adherent[]    findAll()
 * @method Adherent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdherentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adherent::class);
    }

    public function findJoinCompteCotisation(Exercice $exercice)
    {
        return $this->_em->createQuery('select a.id, a.numero, a.nom, c.due, c.paye, c.isPaye from App\Entity\Adherent a inner join App\Entity\CompteCotisation c with c.adherent = a and c.exercice = :e')
                         ->setParameter('e', $exercice)
                         ->getResult()
        ;
    }

    public function findSommePrestationAttente(Exercice $exercice, Adherent $adherent)
    {
        return (int) $this->_em->createQuery('select sum(p.rembourse) as attente from App\Entity\Prestation p where p.adherent = :a and p.isPaye = false and p.date between :dateDebut and :dateFin ')
                         ->setParameter('a', $adherent)
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->setParameter('dateFin', $exercice->getDateFin())
                         ->getSingleScalarResult()
        ;
    }

    public function findJoinPrestationAttente(Exercice $exercice)
    {
        $adherentAvecAttente = $this->_em->createQuery('select a.id, a.nom, a.numero, sum(p.rembourse) as attente from App\Entity\Adherent a, App\Entity\Prestation p where p.adherent = a and p.date between :dateDebut and :dateFin ')
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->setParameter('dateFin', $exercice->getDateFin())
                         ->getResult()
        ;
        return $adherentAvecAttente;
        /*$adherentWithoutAttente = $this->_em->createQuery('select a.id, a.nom, a.numero, 0 as attente from App\Entity\Adherent a left join App\Entity\Prestation p with p.adherent = a and sum(p.rembourse) = 0 and p.isPaye = false and p.date between :dateDebut and :dateFin')
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->setParameter('dateFin', $exercice->getDateFin())
                         ->getResult()
        ;*/

        return array_merge($adherentAvecAttente, $adherentWithoutAttente);
    }

    public function generateNumero()
    {
        // get the last num
        $lastNum = (int) $this->_em->createQuery('select max(a.numero) from App\Entity\Adherent a')
                            ->getSingleScalarResult();
        return $lastNum + 1;
    }

    public function findNbPac(Exercice $exercice, Adherent $adherent)
    {
        return (int) $this->_em->createQuery('select sum(c.nouveau + c.ancien) from App\Entity\CompteCotisation c where c.adherent = :a and c.exercice = :e')
                                    ->setParameter('a', $adherent)
                                    ->setParameter('e', $exercice)
                                    ->getSingleScalarResult();
    }
}
