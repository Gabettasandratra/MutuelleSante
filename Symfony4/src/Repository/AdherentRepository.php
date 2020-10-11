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

    public function findPrestationAttente(Exercice $exercice, Adherent $adherent)
    {
        $montantEnAttentePaiement = floatval($this->_em->createQuery('select sum(p.rembourse) from App\Entity\Prestation p where p.adherent = :a and p.isPaye = false and p.date between :dateDebut and :dateFin ')
                         ->setParameter('a', $adherent)
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->setParameter('dateFin', $exercice->getDateFin())
                         ->getSingleScalarResult())
        ;
        $nbEnAttenteDecision = $this->_em->createQuery('select count(p) from App\Entity\Prestation p where p.adherent = :a and p.status = 0 and p.date between :dateDebut and :dateFin ')
                         ->setParameter('a', $adherent)
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->setParameter('dateFin', $exercice->getDateFin())
                         ->getSingleScalarResult()
        ;
        return ['nonPaye' => $montantEnAttentePaiement, 'nonDecide' => $nbEnAttenteDecision];
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

    public function findByExercice(Exercice $exercice)
    {
        return $this->_em->createQuery('select a from App\Entity\Adherent a where a.dateInscription <= :dateFin')
                        ->setParameter('dateFin', $exercice->getDateFin())
                        ->getResult();
    }

    public function findDateInscription(\DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        return $this->_em->createQuery('select a from App\Entity\Adherent a where a.dateInscription between :deb and :fin')
                        ->setParameter('deb', $debut)
                        ->setParameter('fin', $fin)
                        ->getResult();
    }

    public function findAncien(Exercice $exercice, Adherent $adherent)
    {
        return $this->_em->createQuery('select p from App\Entity\Pac p where p.adherent = :a and p.dateEntrer < :dateDebut')
                         ->setParameter('a', $adherent)
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->getResult()
        ;
    }

    public function findNouveau(Exercice $exercice, Adherent $adherent)
    {
        return $this->_em->createQuery('select p from App\Entity\Pac p where p.adherent = :a and p.dateEntrer between :dateDebut and :dateFin')
                         ->setParameter('a', $adherent)
                         ->setParameter('dateDebut', $exercice->getDateDebut())
                         ->setParameter('dateFin', $exercice->getDateFin())
                         ->getResult()
        ;
    }

    public function findEvolutionCongregation($annee)
    {
        // Les dix années précedents
        $retour = [];
        for ($i=0; $i < 10; $i++) { 
            $year = $annee - $i;
            $retour[$year] = (int) $this->_em->createQuery('select count(a) from App\Entity\Adherent a where year(a.dateInscription) <= :year')
                                                ->setParameter('year', $year)
                                                ->getSingleScalarResult();
        }
        return $retour;      
    }

    public function findEvolutionBeneficiaire($annee)
    {
        // Les dix années précedents
        $retour = [];
        for ($i=0; $i < 10; $i++) { 
            $year = $annee - $i;
            $retour[$year] = (int) $this->_em->createQuery('select count(p) from App\Entity\Pac p where year(p.dateEntrer) <= :year')
                                                ->setParameter('year', $year)
                                                ->getSingleScalarResult();
        }
        return $retour;      
    }


    // seuelement utiliser dans le tableau de bord
    public function findNbAdhAndPac(Exercice $exercice)
    {
        $nbAd = $this->_em->createQuery('select count(a) from App\Entity\Adherent a where a.dateInscription <= :dateFin')
                        ->setParameter('dateFin', $exercice->getDateFin())
                        ->getSingleScalarResult();
        $nbPac = $this->_em->createQuery('select count(p) from App\Entity\Pac p where p.dateEntrer <= :dateFin and p.isSortie = false')
                        ->setParameter('dateFin', $exercice->getDateFin())
                        ->getSingleScalarResult();
        return ['ad' => $nbAd, 'pac' => $nbPac];
    }
}
