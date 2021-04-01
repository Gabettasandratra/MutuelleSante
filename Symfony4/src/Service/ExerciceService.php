<?php

namespace App\Service;

use App\Entity\Exercice;
use App\Entity\CompteCotisation;
use App\Repository\AdherentRepository;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExerciceService
{
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ExerciceRepository $exerciceRepo, AdherentRepository $adherentRepo)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->exerciceRepo = $exerciceRepo;
        $this->adherentRepo = $adherentRepo;
    }

    public function createNewExercice(Exercice $exercice)
    {
        $dernierExercice = $this->exerciceRepo->findDernierExercice();
        $allAdherents = $this->adherentRepo->findAll();
        foreach ($allAdherents as $adherent) {
            $compteCotisation = new CompteCotisation($exercice, $adherent);
            $nbAncien = $this->adherentRepo->findNbPac($dernierExercice, $adherent);
            $compteCotisation->setAncien($nbAncien);

            $this->manager->persist($compteCotisation);  
        }
        
        $this->manager->persist($exercice);  
        $this->manager->flush(); 
    }
}