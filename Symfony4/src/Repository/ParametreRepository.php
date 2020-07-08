<?php

namespace App\Repository;

use App\Entity\Parametre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Parametres|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parametres|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parametres[]    findAll()
 * @method Parametres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParametreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parametre::class);
    }

    /**
    * @return Parametres[] Returns an array of Parametres objects
    */
    public function getParameters()
    {
        $parameters = $this->_em->createQuery('select p from App\Entity\Parametre p')
                                ->getResult(); 
        $retour = [];
        foreach ($parameters as $parameter) {
            $retour[$parameter->getNom()] = $parameter;
        }
        return $retour;
    }
}
