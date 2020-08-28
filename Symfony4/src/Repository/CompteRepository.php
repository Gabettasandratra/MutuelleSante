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

    public function findBilanGroupByClass()
    {
        $classes =  $this->_em->createQuery('select distinct c.classe from App\Entity\Compte c where c.categorie = :cat order by c.classe')
                                ->setParameter('cat', 'COMPTES DE BILAN')                      
                                ->getResult();
        $retour = [];
        foreach ($classes as $classe) {
            $str = $classe['classe'];
            $retour[$str] = $this->_em->createQuery('select c.poste,c.titre,c.type,c.note from App\Entity\Compte c where c.classe = :cl')
                                    ->setParameter('cl', $str)
                                    ->getResult();
        }

        return $retour;
    }

    public function findGestionGroupByClass()
    {
        $classes =  $this->_em->createQuery('select distinct c.classe from App\Entity\Compte c where c.categorie = :cat order by c.classe')
                                ->setParameter('cat', 'COMPTES DE GESTION')                      
                                ->getResult();
        $retour = [];
        foreach ($classes as $classe) {
            $str = $classe['classe'];
            $retour[$str] = $this->_em->createQuery('select c.poste,c.titre,c.type,c.note from App\Entity\Compte c where c.classe = :cl order by c.poste')
                                    ->setParameter('cl', $str)
                                    ->getResult();
        }
        return $retour;
    }

    public function findPosteTitre()
    {
        return $this->_em->createQuery('select c.poste,c.titre from App\Entity\Compte c order by c.poste')
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
        $codes[] = [ 'titre' => 'Opération divers', 'codeJournal' => 'OD'];
        $codes[] = [ 'titre' => 'Clôture d\'exercice', 'codeJournal' => 'CLOT'];
        $codes[] = [ 'titre' => 'Prestation', 'codeJournal' => 'PRE'];
        if ($in) {
            foreach ($codes as $code) {
                if ($code['codeJournal'] == $in) {
                    return [$code];
                }
            }
        }
        return $codes;
    }

    // Cumul de tous les exercices
    public function findBilanActif(Exercice $exercice)
    {
        $comptesBilan =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = true and c.categorie = \'COMPTES DE BILAN\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesBilan as $compte) {
            // Le solde à la date de fin de l'exercice pas dans une période
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date <= :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateFin', $exercice->getDateFin())                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date <= :dateFin')
                            ->setParameter('cp', $compte) 
                            ->setParameter('dateFin', $exercice->getDateFin())                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($debit - $credit) ];
        }

        return $retour;
    }

    // Cumul de tous les exercices
    public function findBilanPassif(Exercice $exercice)
    {
        $comptesBilan =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = false and c.categorie = \'COMPTES DE BILAN\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesBilan as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date <= :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateFin', $exercice->getDateFin())                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date <= :dateFin')
                            ->setParameter('cp', $compte) 
                            ->setParameter('dateFin', $exercice->getDateFin())                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($credit - $debit) ];
        }

        return $retour;
    }

    // Pendant une exercice seulement
    public function findGestionCharge(Exercice $exercice)
    {
        $comptesGestion =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = true and c.categorie = \'COMPTES DE GESTION\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesGestion as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date between :dateDebut and :dateFin and a.categorie != \'CLOT\'')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date between :dateDebut and :dateFin and a.categorie != \'CLOT\'')
                            ->setParameter('cp', $compte) 
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($debit - $credit) ];
        }

        return $retour;
    }

    // Pendant une exercice seulement
    public function findGestionProduit(Exercice $exercice)
    {
        /* On suppose que les comptes de produits ne peuvent */
        $comptesGestion =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = false and c.categorie = \'COMPTES DE GESTION\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesGestion as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date between :dateDebut and :dateFin and a.categorie != \'CLOT\'')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date between :dateDebut and :dateFin and a.categorie != \'CLOT\'')
                            ->setParameter('cp', $compte) 
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($credit - $debit) ];
        }

        return $retour;
    }
}
