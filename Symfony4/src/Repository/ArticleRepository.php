<?php

namespace App\Repository;

use App\Entity\Compte;
use App\Entity\Article;
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

    public function findGrandLivre()
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
                $articles = $this->findGrandLivreCompte($compte);
                if ($articles) {
                    $labelCompte = $compte->getPoste()." ".$compte->getTitre();
                    $retour[$str][$labelCompte] = $articles;
                }
                
            }
        }
        return $retour; 
    }

    public function findGrandLivreCompte(Compte $compte)
    {
        $debit = $this->_em->createQuery('select a.date,a.libelle,a.piece,a.categorie,a.montant as debit,0 as credit from App\Entity\Article a where a.compteDebit = :cp order by a.date DESC')
                                ->setParameter('cp', $compte)                      
                                ->getResult(); 
        $credit = $this->_em->createQuery('select a.date,a.libelle,a.piece,a.categorie,0 as debit,a.montant as credit from App\Entity\Article a where a.compteCredit = :cp order by a.date DESC')
                            ->setParameter('cp', $compte)                      
                            ->getResult(); 
        return array_merge($debit, $credit);

    }

}
