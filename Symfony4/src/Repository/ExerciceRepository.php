<?php

namespace App\Repository;

use App\Entity\Exercice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exercice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exercice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exercice[]    findAll()
 * @method Exercice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExerciceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exercice::class);
    }
    
    public function findCurrent(): ?Exercice
    {
        $exercice = $this->_em->createQuery('select e from App\Entity\Exercice e where CURRENT_DATE() between e.dateDebut and e.dateFin')  
                        ->getOneOrNullResult();
        if (!$exercice) {
            return $this->_em->createQuery('select e from App\Entity\Exercice e order by e.dateFin DESC')  
                        ->getOneOrNullResult();
        }

        return $exercice;
    }

    public function findExerciceFromInscription(Adherent $adherent)
    {
        return $this->_em->createQuery('select e from App\Entity\Exercice e where :dateInscription <= e.dateFin')  
                                        ->setParameter('dateInscription', $adherent->getDateInscription())
                                        ->getResult();   
    }

    public function findFinExercice()
    {
        $result =  $this->_em->createQuery('select max(e.dateFin) from App\Entity\Exercice e')  
                                ->getOneOrNullResult(); 
        if ($result[1] != null) {
            return new \DateTimeImmutable($result[1]);
        } 
        // Quand on n'a trouvé aucun date fin
        return \DateTimeImmutable::createFromFormat('j-m-Y', '31-12-'.(date('Y')-1));
    }

    public function findDernierExercice()
    {
        $dernierDate = $this->_em->createQuery('select max(e.dateFin) from App\Entity\Exercice e')  
                    ->getOneOrNullResult(); 
        return $this->_em->createQuery('select e from App\Entity\Exercice e where e.dateFin = :der')
                    ->setParameter('der', $dernierDate)  
                    ->getOneOrNullResult();   
    }

    public function findResult(Exercice $exercice)
    {
        $charges = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a join App\Entity\Compte c with a.compteDebit = c and c.classe = \'6-COMPTES DE CHARGES\' where a.date between :dateDebut and :dateFin')
                                ->setParameter('dateDebut', $exercice->getDateDebut())                      
                                ->setParameter('dateFin', $exercice->getDateFin())                      
                                ->getSingleScalarResult();
        $produits = (float) $this->_em->createQuery('select sum(a.montant) from App\Entity\Article a join App\Entity\Compte c with a.compteCredit = c and c.classe = \'7-COMPTES DE PRODUITS\' where a.date between :dateDebut and :dateFin')
                                ->setParameter('dateDebut', $exercice->getDateDebut())                      
                                ->setParameter('dateFin', $exercice->getDateFin())                      
                                ->getSingleScalarResult();
        return ($produits - $charges);
    }
}
