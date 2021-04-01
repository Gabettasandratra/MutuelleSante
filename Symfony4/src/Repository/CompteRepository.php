<?php

namespace App\Repository;

use App\Entity\Compte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($debit - $credit) ];
        }

        return $retour;
    }

    public function findBilanActif()
    {
        $comptesBilan =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = true and c.categorie = \'COMPTES DE BILAN\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesBilan as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp')
                                ->setParameter('cp', $compte)                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp')
                            ->setParameter('cp', $compte)                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($debit - $credit) ];
        }

        return $retour;
    }

    public function findBilanPassif()
    {
        $comptesBilan =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = false and c.categorie = \'COMPTES DE BILAN\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesBilan as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp')
                                ->setParameter('cp', $compte)                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp')
                            ->setParameter('cp', $compte)                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($credit - $debit) ];
        }

        return $retour;
    }

    public function findGestionCharge()
    {
        $comptesBilan =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = true and c.categorie = \'COMPTES DE GESTION\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesBilan as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp')
                                ->setParameter('cp', $compte)                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp')
                            ->setParameter('cp', $compte)                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($debit - $credit) ];
        }

        return $retour;
    }

    public function findGestionProduit()
    {
        $comptesBilan =  $this->_em->createQuery('select c from App\Entity\Compte c where c.type = false and c.categorie = \'COMPTES DE GESTION\' order by c.poste')
                                    ->getResult();
        $retour = [];
        foreach ($comptesBilan as $compte) {
            $debit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteDebit = :cp')
                                ->setParameter('cp', $compte)                      
                                ->getSingleScalarResult();
            $credit = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.compteCredit = :cp')
                            ->setParameter('cp', $compte)                      
                            ->getSingleScalarResult();
            $retour[] = ['poste' => $compte->getPoste(), 'titre' => $compte->getTitre(), 'solde' => ($credit - $debit) ];
        }

        return $retour;
    }

    /*
    public function findOneBySomeField($value): ?Compte
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
