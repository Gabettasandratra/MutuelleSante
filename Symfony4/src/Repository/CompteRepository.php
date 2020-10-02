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
        return $this->_em->createQuery('select c from App\Entity\Compte c order by c.poste')
                                    ->getResult();
    }

    public function findPosteTitre()
    {
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
            $retour[] = ['id' => $compte->getId(), 'poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($debit - $credit), 'codeJournal' => $compte->getCodeJournal(), 'isCheque' => $compte->isTresorerieCheque() ];
        }

        return $retour;
    }

    public function findSolde(Compte $compte)
    {
        $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp')
                                ->setParameter('cp', $compte)                      
                                ->getSingleScalarResult();
        $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp')
                            ->setParameter('cp', $compte)                      
                            ->getSingleScalarResult();
        return ($debit - $credit);
    }

    public function findCodeJournaux($in = null)
    {
        $codes =  $this->_em->createQuery('select c.titre, c.codeJournal from App\Entity\Compte c where c.isTresor =  true order by c.codeJournal')
                            ->getResult();
        foreach ($codes as $code) {
            $code['type'] = 'trésorerie';
            $retour[] = $code;
        }
        $retour[] = [ 'titre' => 'opération divers', 'codeJournal' => 'OD','type' => 'standard'];
        $retour[] = [ 'titre' => 'prestations', 'codeJournal' => 'PRE' ,'type' => 'sortie'];
        if ($in) {
            foreach ($retour as $code) {
                if ($code['codeJournal'] == $in) {
                    return [$code];
                }
            }
        }
        return $retour;
    }

    /**
     * Cherche la somme des soldes dans le postes données (Etat financieres)
     */
    public function findSoldes($postes = [], Exercice $exercice)
    {
        $solde = 0;
        foreach ($postes as $poste) {
            $comptes = $this->_em->createQuery('select c from App\Entity\Compte c where length(c.poste) = 6 and c.poste like :p')
                                ->setParameter('p', $poste.'%')                      
                                ->getResult();
            foreach ($comptes as $compte) {
                if ($compte->getClasse() != '6-COMPTES DE CHARGES' && $compte->getClasse() != '7-COMPTES DE PRODUITS') {
                    $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date <= :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateFin', $exercice->getDateFin())                     
                                ->getSingleScalarResult();
                    $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date <= :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateFin', $exercice->getDateFin())                   
                                ->getSingleScalarResult();
                } else {
                    // Les comptes de gestion
                    $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where (a.analytique is null or a.analytique != \'-\') and a.compteDebit = :cp and a.date between :dateDebut and :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateDebut', $exercice->getDateDebut())                     
                                ->setParameter('dateFin', $exercice->getDateFin())                     
                                ->getSingleScalarResult();            
                    
                    $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where (a.analytique is null or a.analytique != \'-\') and a.compteCredit = :cp and a.date between :dateDebut and :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())                                        
                                ->getSingleScalarResult();
                }             
                $solde = $solde + $debit - $credit;
            }
        }
        return $solde;
    }
}
