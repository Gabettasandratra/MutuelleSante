<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Remboursement;
use App\Entity\HistoriqueCotisation;
use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/* Les opérations comptable de la gestion de Mutuelle */
class ComptaService 
{
    private $manager;
    private $validator;
    private $comptaRepo;

    public function __construct(EntityManagerInterface $entityManager, CompteRepository $comptaRepo)
    {
        $this->manager = $entityManager;
        $this->comptaRepo = $comptaRepo;
    }

    /*
    * A chaque versement de cotisation
    */
    public function payCotisation(HistoriqueCotisation $cotisation)
    {
        // Ce compte est provisoire
        $compteCotisation = $this->comptaRepo->find(1);

        $article = new Article();
        $article->setCompteDebit($cotisation->getTresorerie());
        $article->setCompteCredit($compteCotisation);
        $article->setLibelle('Cotisation '. $cotisation->getCompteCotisation()->getExercice()->getAnnee() .'| '. $cotisation->getCompteCotisation()->getAdherent()->getNom());
        $article->setCategorie('Cotisation'); // journal
        $article->setAnalytique('Cotisation');
        $article->setMontant($cotisation->getMontant());
        $article->setDate($cotisation->getDatePaiement());
        $article->setPiece($cotisation->getReference());

        $this->manager->persist($article);
        $this->manager->flush();    
    }

    /*
    * A chaque remboursement de prestation
    */
    public function payRemboursement(Remboursement $remboursement)
    {
        // Ce compte est provisoire
        $compteRemboursement = $this->comptaRepo->find(4);

        $article = new Article();
        $article->setCompteDebit($compteRemboursement);        
        $article->setCompteCredit($remboursement->getTresorerie());        
        $article->setLibelle('Remboursement prestation '. $remboursement->getAdherent()->getNom());
        $article->setCategorie('Remboursement'); // journal
        $article->setAnalytique('Remboursement');
        $article->setMontant($remboursement->getMontant());
        $article->setDate($remboursement->getDate());
        $article->setPiece($remboursement->getReference());

        $this->manager->persist($article);
        $this->manager->flush();    
    }
}