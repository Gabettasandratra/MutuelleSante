<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Remboursement;
use App\Service\ParametreService;
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

    public function __construct(ParametreService $paramService, EntityManagerInterface $entityManager, CompteRepository $comptaRepo)
    {
        $this->manager = $entityManager;
        $this->comptaRepo = $comptaRepo;
        $this->paramService = $paramService;
    }

    /*
    * A chaque versement de cotisation
    */
    public function payCotisation(HistoriqueCotisation $cotisation)
    {
        // Charger le compte depuis le parametre
        $posteCotisation = $this->paramService->getParametre('compte_cotisation');
        $compteCotisation = $this->comptaRepo->findOneByPoste($posteCotisation);
        $label = $this->paramService->getParametre('label_cotisation');
        
        $label = str_ireplace('{a}', $cotisation->getCompteCotisation()->getExercice()->getAnnee(), $label);
        $label = str_ireplace('{c}', $cotisation->getCompteCotisation()->getAdherent()->getNom(), $label);

        $article = new Article();
        $article->setCompteDebit($cotisation->getTresorerie());
        $article->setCompteCredit($compteCotisation);
        $article->setLibelle($label);
        $article->setCategorie('Cotisation'); // journal
        $article->setAnalytique('Cotisation');
        $article->setMontant($cotisation->getMontant());
        $article->setDate($cotisation->getDatePaiement());
        $article->setPiece($cotisation->getReference());

        $cotisation->setArticle($article);

        $this->manager->persist($cotisation); // seul persist suffit
        $this->manager->flush();    

        return $cotisation;
    }

    /*
    * A chaque remboursement de prestation
    */
    public function payRemboursement(Remboursement $remboursement)
    {
        $posteRemboursement = $this->paramService->getParametre('compte_prestation');
        $compteRemboursement = $this->comptaRepo->findOneByPoste($posteRemboursement);
        $label = $this->paramService->getParametre('label_prestation');
        
        $label = str_replace('{a}', $remboursement->getExercice()->getAnnee(), $label);
        $label = str_replace('{c}', $remboursement->getAdherent()->getNom(), $label);

        $article = new Article();
        $article->setCompteDebit($compteRemboursement);        
        $article->setCompteCredit($remboursement->getTresorerie());        
        $article->setLibelle($label);
        $article->setCategorie('Remboursement'); // journal
        $article->setAnalytique('Remboursement');
        $article->setMontant($remboursement->getMontant());
        $article->setDate($remboursement->getDate());
        $article->setPiece($remboursement->getReference());

        $remboursement->setArticle($article);

        $this->manager->persist($remboursement);
        $this->manager->flush();    

        return $remboursement;
    }
}