<?php

namespace App\Repository;

use App\Entity\Adherent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        $adherent->setNbNouveau($this->getNbNouveauBeneficiaires($adherent));
        $adherent->setNbAncien($this->getNbAncienBeneficiaires($adherent));

        return $adherent;
    }

    public function generateNumero()
    {
        // get the last num
        $lastNum = (int) $this->_em->createQuery('select max(a.numero) from App\Entity\Adherent a')
                            ->getSingleScalarResult();
        return $lastNum + 1;
    }

    public function getNbNouveauBeneficiaires(Adherent $adherent)
    {

        $nb = $this->_em->createQuery('select count(p) from App\Entity\Pac p where p.adherent = :val and p.isSortie = false and year(p.dateEntrer) = year(CURRENT_DATE()) ')
                        ->setParameter('val', $adherent)
                        ->getSingleScalarResult();
        return $nb;
    }

    public function getNbAncienBeneficiaires(Adherent $adherent)
    {

        $nb = $this->_em->createQuery('select count(p) from App\Entity\Pac p where p.adherent = :val and p.isSortie = false and year(p.dateEntrer) < year(CURRENT_DATE()) ')
                        ->setParameter('val', $adherent)
                        ->getSingleScalarResult();
        return $nb;
    }
}
