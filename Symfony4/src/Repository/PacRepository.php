<?php

namespace App\Repository;

use App\Entity\Pac;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Adherent;

/**
 * @method Pac|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pac|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pac[]    findAll()
 * @method Pac[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PacRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pac::class);
    }

    public function generateCode(Adherent $adherent)
    {
        // get the last id
        $lastCode = $this->_em->createQuery('select max(p.codeMutuelle) from App\Entity\Pac p where p.adherent = :a')
                            ->setParameter('a', $adherent) 
                            ->getSingleScalarResult();
        $id = (int)str_replace($adherent->getId().'/', '', $lastCode);
        if ($id == 0) {
            $id = 1;
        }
        return $adherent->getId().'/'.++$id;
    }
}
