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

    public function findGrandLivre(Exercice $exercice)
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
                $articles = $this->findGrandLivreCompte($exercice, $compte);
                if ($articles) {
                    $labelCompte = $compte->getPoste()." ".$compte->getTitre();
                    $retour[$str][$labelCompte] = $articles;
                }
            }
        }
        return $retour; 
    }

    public function findGrandLivreCompte(Exercice $exercice,Compte $compte)
    {
        $debit = $this->_em->createQuery('select a.date,a.libelle,a.piece,a.categorie,a.montant as debit,0 as credit from App\Entity\Article a where a.compteDebit = :cp and a.date between :dateDebut and :dateFin order by a.date DESC')
                                ->setParameter('cp', $compte)  
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())                    
                                ->getResult(); 
        $credit = $this->_em->createQuery('select a.date,a.libelle,a.piece,a.categorie,0 as debit,a.montant as credit from App\Entity\Article a where a.compteCredit = :cp and a.date between :dateDebut and :dateFin order by a.date DESC')
                            ->setParameter('cp', $compte)    
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())                  
                            ->getResult(); 
        $list = array_merge($debit, $credit);

        usort($list, function($a, $b){
            if ($a['date'] === $b['date'] ) {
                return 0;
            }
            return ($a['date'] > $b['date']) ? -1 : 1 ;
        });

        return $list;
    }

    public function findBalance(Exercice $exercice)
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
                if ($compte->getCategorie() != 'COMPTES DE GESTION') {
                    $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date < :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateFin', $exercice->getDateFin())                     
                                ->getSingleScalarResult();
                    $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date < :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateFin', $exercice->getDateFin())                   
                                ->getSingleScalarResult();
                } else {
                    $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp and a.date between :dateDebut and :dateFin')
                                ->setParameter('cp', $compte) 
                                ->setParameter('dateDebut', $exercice->getDateDebut())                     
                                ->setParameter('dateFin', $exercice->getDateFin())                     
                                ->getSingleScalarResult();
                    $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp and a.date between :dateDebut and :dateFin')
                                ->setParameter('cp', $compte)   
                                ->setParameter('dateDebut', $exercice->getDateDebut())
                                ->setParameter('dateFin', $exercice->getDateFin())                                        
                                ->getSingleScalarResult();
                }             
                $retour[$str][] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'debit' => $debit, 'credit' => $credit, 'solde' => ($debit - $credit)];               
            }
            
        }
        return $retour; 
    }

    public function findJournal(Exercice $exercice, $journal = null)
    {
        if ($journal === null) {
            return $this->_em->createQuery('select a from App\Entity\Article a where a.date between :dateDebut and :dateFin order by a.date DESC, a.id DESC')
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getResult(); 
        }
        return $this->_em->createQuery('select a from App\Entity\Article a where a.categorie = :cat and a.date between :dateDebut and :dateFin order by a.date DESC, a.id DESC')
                            ->setParameter('cat', $journal)                      
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())
                            ->getResult(); 
    }
}
