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
