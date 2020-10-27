<?php

namespace App\Repository;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Exercice;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function findGrandLivre(\DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        $classes =  $this->_em->createQuery('select distinct c.classe from App\Entity\Compte c order by c.classe')
                                ->getResult();
        $retour = [];
        foreach ($classes as $classe) {
            $str = $classe['classe'];
            $comptes = $this->_em->createQuery('select c from App\Entity\Compte c where c.classe = :cl order by c.poste')
                                    ->setParameter('cl', $str)
                                    ->getResult();
            foreach ($comptes as $compte) {
                $livre = $this->findGrandLivreCompte($debut, $fin, $compte);
                if ($livre) {               
                    $retour = array_merge_recursive($retour,$livre);
                }
            }
        }
        return $retour; 
    }

    public function findGrandLivreCompte(\DateTimeInterface $debut, \DateTimeInterface $fin,Compte $compte)
    {
        $debit = $this->_em->createQuery('select a.id,a.date,a.libelle,a.piece,a.categorie,a.montant as debit,0 as credit from App\Entity\Article a where a.compteDebit = :cp and a.date between :dateDebut and :dateFin order by a.date DESC')
                                ->setParameter('cp', $compte)  
                                ->setParameter('dateDebut', $debut)
                                ->setParameter('dateFin', $fin)                    
                                ->getResult(); 
        $credit = $this->_em->createQuery('select a.id,a.date,a.libelle,a.piece,a.categorie,0 as debit,a.montant as credit from App\Entity\Article a where a.compteCredit = :cp and a.date between :dateDebut and :dateFin order by a.date DESC')
                            ->setParameter('cp', $compte)    
                            ->setParameter('dateDebut', $debut)
                            ->setParameter('dateFin', $fin)                  
                            ->getResult(); 
        $articles = array_merge($debit, $credit);

        usort($articles, function($a, $b){
            if ($a['date'] === $b['date'] ) {
                return 0;
            }
            return ($a['date'] > $b['date']) ? -1 : 1 ;
        });

        if ($articles) {
            $labelCompte = $compte->getPoste()." ".$compte->getTitre();
            $retour[$compte->getClasse()][$labelCompte] = $articles;
            return $retour;
        }
        return null;
    }

    public function findBalance(\DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        $classes =  $this->_em->createQuery('select distinct c.classe from App\Entity\Compte c order by c.classe')
                                ->getResult();
        $retour = [];
        foreach ($classes as $classe) {
            $str = $classe['classe'];
            $comptes = $this->_em->createQuery('select c from App\Entity\Compte c where c.classe = :cl and length(c.poste) = 6 order by c.poste')
                                    ->setParameter('cl', $str)
                                    ->getResult();          
            
            foreach ($comptes as $compte) {
                /* Ajoute le solde initiale */
                $soldeInit = 0; 
                if ($compte->getClasse() != '6-COMPTES DE CHARGES' && $compte->getClasse() != '7-COMPTES DE PRODUITS') {
                    $debitI = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date < :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateFin', $debut)                     
                                ->getSingleScalarResult();
                    $creditI = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date < :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateFin', $debut)                   
                                ->getSingleScalarResult();
                    $soldeInit = $debitI - $creditI;
                } 

                $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date between :dateDebut and :dateFin')
                            ->setParameter('cp', $compte) 
                            ->setParameter('dateDebut', $debut)                     
                            ->setParameter('dateFin', $fin)                     
                            ->getSingleScalarResult();
                $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date between :dateDebut and :dateFin')
                            ->setParameter('cp', $compte)   
                            ->setParameter('dateDebut', $debut)
                            ->setParameter('dateFin', $fin)                                        
                            ->getSingleScalarResult();
                     
                // On ajoute le solde initiale
                if ($soldeInit < 0)
                    $credit -= $soldeInit;
                else
                    $debit += $soldeInit;

                if (($debit - $credit) <= 0) {
                    $soldeD = 0; $soldeC = - $debit + $credit;
                } else {
                    $soldeC = 0; $soldeD = $debit - $credit;
                }

                // On n'affiche pas les soldes zero
                if ($debit != 0 || $credit != 0)
                    $retour[$str][] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'soldeInit' => $soldeInit, 'debit' => $debit, 'credit' => $credit, 'soldeD' => $soldeD, 'soldeC' => $soldeC];               
            }
            
        }
        return $retour; 
    }

    public function findJournal($journal, \DateTimeInterface $debut, \DateTimeInterface $fin)
    {
        return $this->_em->createQuery('select a from App\Entity\Article a where a.categorie = :cat and a.date between :dateDebut and :dateFin order by a.date desc, a.id desc')
                            ->setParameter('cat', $journal)                      
                            ->setParameter('dateDebut', $debut)
                            ->setParameter('dateFin', $fin)
                            ->getResult(); 
    }

    public function findCheques(Exercice $exercice, Compte $compteCheque)
    {
        $cheques = $this->_em->createQuery('select a from App\Entity\Article a where a.compteDebit = :c and a.date between :dateDebut and :dateFin')
                ->setParameter('c', $compteCheque)
                ->setParameter('dateDebut', $exercice->getDateDebut())
                ->setParameter('dateFin', $exercice->getDateFin())
                ->getResult();
        $retour = [];
        foreach ($cheques as $cheque) {
            // Check if verser : montant, libelle
            $versement = $this->_em->createQuery('select a from App\Entity\Article a where a.compteCredit = :c and a.montant = :m and a.libelle = :l and a.date between :dateDebut and :dateFin')
                ->setParameter('c', $compteCheque)
                ->setParameter('m', $cheque->getMontant())
                ->setParameter('l', "Versement chèque: ".$cheque->getPiece())
                ->setParameter('dateDebut', $exercice->getDateDebut())
                ->setParameter('dateFin', $exercice->getDateFin())
                ->getOneOrNullResult();
            $retour[] = ['id' => $cheque->getId(), 'numero' => $cheque->getPiece(), 'montant' => $cheque->getMontant(), 'utilisation' => $cheque->getLibelle(), 'versement' => $versement];
        }

        return $retour;
    }
}
