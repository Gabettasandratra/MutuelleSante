<?php

namespace App\Repository;

use App\Entity\Compte;
use App\Entity\Exercice;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Compte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Compte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Compte[]    findAll()
 * @method Compte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compte::class);
    }

    public function findComptes()
    {       
        return $this->_em->createQuery('select c from App\Entity\Compte c where length(c.poste)=6 order by c.poste')
                                    ->getResult();
    }

    public function findPosteTitre($begin = null)
    {
        if ($begin) {
            return $this->_em->createQuery('select c.poste,c.titre from App\Entity\Compte c where length(c.poste) = 6 and c.poste like :b order by c.poste')
                        ->setParameter('b', $begin)                                  
                        ->getResult();
        }
        return $this->_em->createQuery('select c.poste,c.titre from App\Entity\Compte c where length(c.poste) = 6 order by c.poste')
                        ->getResult();
    }

    public function findTresorerie()
    {
        $comptesTresor =  $this->_em->createQuery('select c from App\Entity\Compte c where c.isTresor =  true order by c.poste')
                        ->getResult();
        $retour = [];
        foreach ($comptesTresor as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp')
                                ->setParameter('cp', $compte)                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp')
                            ->setParameter('cp', $compte)                      
                            ->getSingleScalarResult();
            $retour[] = ['id' => $compte->getId(), 'poste' => $compte->getPoste(), 'titre' => $compte->getTitre(),'note' => $compte->getNote(), 'solde' => ($debit - $credit), 'codeJournal' => $compte->getCodeJournal(), 'isCheque' => $compte->isTresorerieCheque() ];
        }

        return $retour;
    }

    public function findSoldeTresorerieParMois($exercice)
    {
        $annee = $exercice->getAnnee();  
        $now = new \DateTime(); 
        for ($i=2; $i <= 13; $i++) { 
            $date = \DateTime::createFromFormat('d/m/Y', "1/$i/$annee")->modify('-1 day');
            if ($date < $now || ($i-1) <= date('m')) {
                $solde[] = $this->findSoldes(['51','53'],$exercice,$date);
            }
        }
        return $solde;  
    }

    public function findSolde(Compte $compte, \DateTimeInterface $date = null)
    {
        if (!$date) 
            $date = new \DateTime();
        
        $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date < :date')
                                ->setParameter('cp', $compte)                      
                                ->setParameter('date', $date)                      
                                ->getSingleScalarResult();
        $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date < :date')
                            ->setParameter('cp', $compte)  
                            ->setParameter('date', $date)                                          
                            ->getSingleScalarResult();
        return ($debit - $credit);
    }

    /**
     * Cherche la somme des soldes dans le postes données (Etat financieres)
     */
    public function findSoldes($postes = [], Exercice $exercice, \DateTimeInterface $dateFin = null, \DateTimeInterface $dateDebut = null)
    {
        if (!$dateFin) 
            $dateFin = $exercice->getDateFin();
        if (!$dateDebut) 
            $dateDebut = $exercice->getDateDebut();                    
        
        $solde = 0;
        foreach ($postes as $poste) {
            $comptes = $this->_em->createQuery('select c from App\Entity\Compte c where length(c.poste) = 6 and c.poste like :p')
                                ->setParameter('p', $poste.'%')                      
                                ->getResult();
            foreach ($comptes as $compte) {
                if ($compte->getClasse() != '6-COMPTES DE CHARGES' && $compte->getClasse() != '7-COMPTES DE PRODUITS') {
                    $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and date(a.date) <= :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateFin', $dateFin)                     
                                ->getSingleScalarResult();
                    $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and date(a.date) <= :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateFin', $dateFin)                   
                                ->getSingleScalarResult();
                } else {
                    // Les comptes de gestion
                    $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and date(a.date) between :dateDebut and :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateDebut', $dateDebut)                     
                                ->setParameter('dateFin', $dateFin)                     
                                ->getSingleScalarResult();            
                    
                    $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and date(a.date) between :dateDebut and :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateDebut', $dateDebut)
                                ->setParameter('dateFin', $dateFin)                                        
                                ->getSingleScalarResult();
                }             
                $solde = $solde + $debit - $credit;
            }
        }
        return $solde;
    }

    /** OHATRAN'NY TSY ILAINA LOATRA IREO  Fa aveloako eo sao dia ampiana ihany */
    public function findRepartitionCharge($exercice)
    {
        $charges = $this->_em->createQuery("select c from App\Entity\Compte c where c.classe = '6-COMPTES DE CHARGES' and length(c.poste) = 6")
                            ->getResult();
        $totalCharges = $this->findSoldes(['6'], $exercice);
        foreach ($charges as $charge) {
            $solde = $this->findSoldes([$charge->getPoste()], $exercice);
            $compte[] = ['charge'=>$charge,'solde'=>$solde, 'pourcent'=>round($solde/$totalCharges,4)];
        }

        return $compte;
    }

    public function findChargeProduitParMois($exercice)
    {
        $annee = $exercice->getAnnee();    
        $now = new \DateTime(); 
        for ($i=2; $i <= 13; $i++) { 
            
            $dateFin = \DateTime::createFromFormat('d/m/Y', "1/$i/$annee")->modify('-1 day');
            if ($dateFin < $now || ($i-1) <= date('m')) {
                $montCharge = $this->findSoldes(['6'],$exercice, $dateFin);
                $solde['charge'][] = $montCharge;

                $montProduit = - $this->findSoldes(['7'],$exercice, $dateFin);
                $solde['produit'][] =  $montProduit;

                $solde['resultat'][] = $montProduit - $montCharge;
            }
        }
        return $solde;
    }

}
