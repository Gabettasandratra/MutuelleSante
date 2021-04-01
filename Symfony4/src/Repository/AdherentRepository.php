<?php

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\Exercice;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Adherent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adherent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adherent[]    findAll()
 * @method Adherent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdherentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adherent::class);
    }

    public function findOneById($value): ?Adherent
    {
        $adherent = $this->createQueryBuilder('a')
            ->andWhere('a.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        return $adherent;
    }

    public function generateNumero()
    {
        // get the last num
        $lastNum = (int) $this->_em->createQuery('select max(a.numero) from App\Entity\Adherent a')
                            ->getSingleScalarResult();
        return $lastNum + 1;
    }

    public function findNbPac(Exercice $exercice, Adherent $adherent)
    {
        return (int) $this->_em->createQuery('select sum(c.nouveau + c.ancien) from App\Entity\CompteCotisation c where c.adherent = :a and c.exercice = :e')
                                    ->setParameter('a', $adherent)
                                    ->setParameter('e', $exercice)
                                    ->getSingleScalarResult();
    }
}
