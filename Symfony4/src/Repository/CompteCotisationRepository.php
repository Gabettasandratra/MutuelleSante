<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Entity\CompteCotisation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method CompteCotisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompteCotisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompteCotisation[]    findAll()
 * @method CompteCotisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteCotisationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompteCotisation::class);
    }

    /**
    * @return CompteCotisation[] Returns an array of CompteCotisation objects
    */
    public function findCompteCotisation(Adherent $adherent, Exercice $exercice)
    {
        return $this->_em->createQuery('select c from App\Entity\CompteCotisation c where c.adherent = :a and c.exercice = :e')
                            ->setParameter('a', $adherent)
                            ->setParameter('e', $exercice)
                            ->getOneOrNullResult();
    }
}
