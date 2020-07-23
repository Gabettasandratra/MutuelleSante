<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Exercice;
use App\Entity\CompteCotisation;
use App\Repository\CompteRepository;
use App\Repository\AdherentRepository;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExerciceService
{
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ExerciceRepository $exerciceRepo, AdherentRepository $adherentRepo, CompteRepository $compteRepo)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->exerciceRepo = $exerciceRepo;
        $this->adherentRepo = $adherentRepo;
        $this->compteRepo = $compteRepo;
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

        return $exercice;
    }

    public function cloturerExercice(Exercice $exercice)
    {
        $comptes_gestions = $this->compteRepo->findBy(['categorie' => 'COMPTES DE GESTION']);
        // le resulat de l'exercice
        $result = $this->exerciceRepo->findResult($exercice);
        
        if ($result >= 0) { // benefice
            $compteResultat = $this->compteRepo->findOneBy(['poste' => '120000']);
        } else {
            $compteResultat = $this->compteRepo->findOneBy(['poste' => '129000']);
        }

        foreach ($comptes_gestions as $compte) {
            $solde = $this->compteRepo->findSolde($compte);
            if ($solde != 0) {
                $article = new Article();
                $article->setCategorie('CLOT')
                        ->setAnalytique('-')
                        ->setLibelle('Clôture exercice '. $exercice->getAnnee())
                        ->setPiece('Clôture '.$exercice->getAnnee() .' '. date('d/m/Y'))
                        ->setDate(new \DateTime())
                        ->setMontant(abs($solde))
                        ->setIsFerme(true);
                if ($solde >= 0) { // debiteur
                    $article->setCompteDebit($compteResultat)
                            ->setCompteCredit($compte);
                } else { // crediteur
                    $article->setCompteDebit($compte)
                            ->setCompteCredit($compteResultat);
                }
                $this->manager->persist($article); 
            } 
        }

        $exercice->setIsCloture(true);
        $this->manager->flush(); 
        return $exercice;
    }
  
}