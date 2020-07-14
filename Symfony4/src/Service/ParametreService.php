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
        $parametres[] = new Parametre('label_cotisation', 'Cotisation {a} | {c}');
        $parametres[] = new Parametre('analytique_cotisation', 'COT');
        $parametres[] = new Parametre('compte_prestation');
        $parametres[] = new Parametre('label_prestation', 'Remboursement {a} | {c}');
        $parametres[] = new Parametre('analytique_prestation', 'REMB');
        $parametres[] = new Parametre('percent_prestation', 1);
        $parametres[] = new Parametre('plafond_prestation', 2);
        
        $soins = new Parametre('soins_prestation');
        $soins->setList([
            'DENTAIRE' => 'Soins dentaires',
            'AUTRES' => 'Autres soins'
        ]); 
        $parametres[] = $soins;

        $analytiques = new Parametre('plan_analytique');
        $analytiques->setList([
            'BUR' => 'Bureau',
        ]); 
        
        $parametres[] = $analytiques;


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