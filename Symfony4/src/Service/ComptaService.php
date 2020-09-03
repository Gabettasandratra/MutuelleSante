<?php

namespace App\Service;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Remboursement;
use App\Service\ParametreService;
use App\Entity\HistoriqueCotisation;
use App\Repository\CompteRepository;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/* Les opérations comptable de la gestion de Mutuelle */
class ComptaService 
{
    private $manager;
    private $validator;
    private $comptaRepo;

    public function __construct(SessionInterface $session, ParametreService $paramService, EntityManagerInterface $entityManager, CompteRepository $comptaRepo, PrestationRepository $prestationRepo)
    {
        $this->manager = $entityManager;
        $this->session = $session;
        $this->comptaRepo = $comptaRepo;
        $this->prestationRepo = $prestationRepo;
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
        $article->setCategorie($cotisation->getTresorerie()->getCodeJournal()); // journal
        $article->setMontant($cotisation->getMontant());
        $article->setDate($cotisation->getDatePaiement());
        $article->setPiece($cotisation->getReference());
        $article->setIsFerme(true); // No modifiable depuis journal

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
        $posteRemboursement = $this->paramService->getParametre('compte_dette_prestation');
        $compteRemboursement = $this->comptaRepo->findOneByPoste($posteRemboursement);
        $label = $this->paramService->getParametre('label_prestation');
        
        $label = str_replace('{a}', $remboursement->getExercice()->getAnnee(), $label);
        $label = str_replace('{c}', $remboursement->getAdherent()->getNom(), $label);

        $article = new Article();
        $article->setCompteDebit($compteRemboursement);        
        $article->setCompteCredit($remboursement->getTresorerie());        
        $article->setLibelle($label);
        $article->setCategorie($remboursement->getTresorerie()->getCodeJournal());
        $article->setAnalytique($remboursement->getAdherent()->getCodeAnalytique()); // Le congrégation rembourser
        $article->setMontant($remboursement->getMontant());
        $article->setDate($remboursement->getDate());
        $article->setPiece($remboursement->getReference());
        $article->setIsFerme(true); // No modifiable depuis journal

        $remboursement->setArticle($article);

        $this->manager->persist($remboursement);
        $this->manager->flush();    

        return $remboursement;
    }

    /**
     * Le but c'est d'enregistrer en tant que dette les prestations décidé de remboursé 
     */
    public function updateDetteRemb($journal ='PRE')
    {
        $posteRemboursement = $this->paramService->getParametre('compte_prestation'); // Charge
        $compteRemboursement = $this->comptaRepo->findOneByPoste($posteRemboursement); 

        $posteRembDette = $this->paramService->getParametre('compte_dette_prestation'); // Dette des prestations
        $compteRembDette = $this->comptaRepo->findOneByPoste($posteRembDette);

        // A chaque prestation décidé on enregistrer une article correspondant
        $prestationNoEcris = $this->prestationRepo->findNoEcriture();

        foreach ($prestationNoEcris as $prestation) {
            $article = new Article();
            $article->setCompteDebit($compteRemboursement);        
            $article->setCompteCredit($compteRembDette); 
            $label = "Préstation cong: ".$prestation->getAdherent()->getNom()." |bén: ".$prestation->getPac()->getMatricule();
            $article->setLibelle($label);
            $article->setCategorie($journal);

            $article->setMontant($prestation->getRembourse());
            $article->setDate(new \DateTime());
            $piece = $prestation->getAdherent()->getNumero() ."/". $this->session->get('exercice')->getAnnee();
            $article->setPiece($piece . "/" . $prestation->getDecompte()); // Le decompte de prestation
            $article->setIsFerme(true);

            $prestation->setDateDecision(new \DateTime());
            $this->manager->persist($article);
        }       
        $this->manager->flush();       
    }

    public function verserCheque(Article $articleCheque, Compte $compteBanque, \DateTime $date, $borderaux)
    {
        $article = new Article();
        $article->setCompteDebit($compteBanque);        
        $article->setCompteCredit($articleCheque->getCompteDebit());        
        $article->setLibelle("Versement chèque: ". $articleCheque->getPiece());
        $article->setCategorie($compteBanque->getCodeJournal()); // journal de la banque
        $article->setMontant($articleCheque->getMontant());
        $article->setDate($date);
        $article->setPiece($borderaux);
        $article->setIsFerme(true);

        $this->manager->persist($article);
        $this->manager->flush();

        return $article;
    }
}