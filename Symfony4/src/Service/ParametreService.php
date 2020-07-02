<?php

namespace App\Service;

use App\Entity\Parametre;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParametreService
{
    private $manager;
    private $validator;
    private $exerciceRepo;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ParametreRepository $parametreRepo)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->parametreRepo = $parametreRepo;
    }

    public function initialize()
    {
        // Parametres comptable 
        $parametres = [];
        
        $parametres[] = new Parametre('compte_cotisation');
        $parametres[] = new Parametre('compte_prestation');

        foreach ($parametres as $parametre) {
            $this->manager->persist($parametre);  
        }

        $this->manager->flush();

    }

    public function getParametre($nom)
    {
        return $this->parametreRepo->findOneByNom($nom)->getValue();
    }
}