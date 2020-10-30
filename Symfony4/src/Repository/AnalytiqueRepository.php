<?php

namespace App\Repository;

use App\Entity\Exercice;
use App\Entity\Analytique;
use App\Repository\AnalytiqueRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Analytique|null find($id, $lockMode = null, $lockVersion = null)
 * @method Analytique|null findOneBy(array $criteria, array $orderBy = null)
 * @method Analytique[]    findAll()
 * @method Analytique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnalytiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Analytique::class);
    }

    /**
     * DESCRIPTION
     * - un compte analytique est toujours la destination 
     * - les comptes possibles avec un analytique est 2 (Immobilisation), 3 (Stock), 6 (Charge) 
     * - on parle toujours de dépense pas de recette
     */
    public function findAnalytics(Exercice $exercice)
    {
        $ans = $this->_em->createQuery('select t from App\Entity\Analytique t')
                    ->getResult(); 
        foreach ($ans as $an) {
            $cout = (float)$this->_em->createQuery('select sum(a.montant) from App\Entity\Article a where a.analytic = :an and a.date between :dateDebut and :dateFin')
                            ->setParameter('an',$an)   
                            ->setParameter('dateDebut', $exercice->getDateDebut())
                            ->setParameter('dateFin', $exercice->getDateFin())        
                            ->getSingleScalarResult(); 
            $out[] = ['id'=>$an->getId(),'code'=>$an->getCode(),'libelle'=>$an->getLibelle(),'cout'=>$cout];
        }

        return $out;
    }

    public function findServiceSante()
    {
        return $this->_em->createQuery('select t.id, t.code, t.libelle from App\Entity\Analytique t where t.isServiceSante = true')
                    ->getArrayResult(); 
    }
}
