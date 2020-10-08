<?php

namespace App\Repository;

use App\Entity\Pac;
use App\Entity\Adherent;
use App\Entity\Exercice;
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

    public function findNoEcriture()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = 1')
            ->andWhere('p.dateDecision is NULL')
            ->orderBy('p.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function generateNumero($pac, $exercice)
    {
        // Le nombre de décompte arrrivé pour le personne
        $lastNum = (int) $this->_em->createQuery('select max(p.decompte) from App\Entity\Prestation p where p.pac = :pac and p.date between :dateDebut and :dateFin')
                                    ->setParameter('pac', $pac)
                                    ->setParameter('dateDebut', $exercice->getDateDebut())
                                    ->setParameter('dateFin', $exercice->getDateFin())
                                    ->getSingleScalarResult();
        return $lastNum + 1;
    }

    public function getMontantNotPayed(Adherent $adherent)
    {
        return $this->_em->createQuery('select sum(p.frais),sum(p.rembourse) from App\Entity\Prestation p where p.adherent = :ad and p.isPaye = false')
                            ->setParameter('ad', $adherent)
                            ->getResult();                    
    }

    public function findPrestation(Exercice $exercice, Pac $pac)
    {
        return $this->_em->createQuery('select p from App\Entity\Prestation p where p.pac = :pac and p.date between :dateDebut and :dateFin')
                            ->setParameter('pac', $pac)
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getResult();                    
    }

    public function findMontantPayedEachMonth($annee)
    {
        // Les douze mois de l'anneé
        $retour = [];
        for ($i=1; $i <= 12; $i++) { 
            $retour[$i] = $this->_em->createQuery('select sum(p.frais) as t_frais, avg(p.frais) as m_frais, sum(p.rembourse) as t_remb, avg(p.rembourse) as m_remb from App\Entity\Prestation p where year(p.date) = :year and month(p.date) = :month')
                                                ->setParameter('year', $annee)
                                                ->setParameter('month', $i)
                                                ->getOneOrNullResult();
        }
        return $retour;  
    }

    public function findPercentActe($exercice)
    {
        # Total des prestations dans une anneé
        $total = $this->_em->createQuery('select count(p) from App\Entity\Prestation p where p.date between :dateDebut and :dateFin')
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())
                                ->getSingleScalarResult();
        $listDesSoins = $this->_em->createQuery('select p.list from App\Entity\Parametre p where p.nom = \'soins_prestation\'')
                                ->getOneOrNullResult();
        # A chaque soins
        $retour = [];
        
        foreach ($listDesSoins['list'] as $code => $description) {
            $nb = $this->_em->createQuery('select count(p) from App\Entity\Prestation p where p.date between :dateDebut and :dateFin and p.designation = :code')
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())
                                ->setParameter('code', $code)
                                ->getSingleScalarResult();
            
            $value = ($total == 0)? 0: round($nb / $total, 4);
            $retour[] = ['code'=>$code,'description'=>$description,'value'=>$value];
        }
        return $retour;
    }

    // utilisées seulement dans le dashboard
    public function findTauxRemb($exercice)
    {
        //taux de remboursement moyenne, cela n'inclus pas les non remboursés
        $taux =  $this->_em->createQuery('select sum(p.rembourse)/sum(p.frais) from App\Entity\Prestation p where p.status = 1 and p.date between :dateDebut and :dateFin')
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getSingleScalarResult();   
        $enAttente =  $this->_em->createQuery('select sum(p.rembourse) from App\Entity\Prestation p where p.status = 1 and p.isPaye = false and p.date between :dateDebut and :dateFin')
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())
                                ->getSingleScalarResult();
        return ['taux'=>$taux, 'attente'=>$enAttente];
    }

    public function findStatRemb($exercice)
    {
        $remb = $this->_em->createQuery('select count(p) from App\Entity\Prestation p where p.status = 1 and p.date between :dateDebut and :dateFin')
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getSingleScalarResult();
        $nonRemb = $this->_em->createQuery('select count(p) from App\Entity\Prestation p where p.status = -1 and p.date between :dateDebut and :dateFin')
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getSingleScalarResult();
        $nonDecide = $this->_em->createQuery('select count(p) from App\Entity\Prestation p where p.status = 0 and p.date between :dateDebut and :dateFin')
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getSingleScalarResult();   
        return ['remb'=>$remb,'nonRemb'=>$nonRemb,'nonDecide'=>$nonDecide];                    
    }
}
