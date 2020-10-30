<?php

namespace App\Service;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Remboursement;
use App\Service\ParametreService;
use App\Entity\HistoriqueCotisation;
use App\Repository\BudgetRepository;
use App\Repository\CompteRepository;
use App\Repository\AnalytiqueRepository;
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
    private $analyticRepo;

    public function __construct(SessionInterface $session, ParametreService $paramService, EntityManagerInterface $entityManager, CompteRepository $comptaRepo, BudgetRepository $repoBudget, AnalytiqueRepository $analyticRepo,PrestationRepository $prestationRepo)
    {
        $this->manager = $entityManager;
        $this->session = $session;
        $this->budgetRepo = $repoBudget;
        $this->comptaRepo = $comptaRepo;
        $this->analyticRepo = $analyticRepo;
        $this->prestationRepo = $prestationRepo;
        $this->paramService = $paramService;
    }

    /*
    * IMPUTATION DE COTISATION
    * - ne passe pas par le compte tiers 4 donc pas de compte Tiers associé
    * - pas de charge mais poduits donc pas necessaire le compte Analytique
    * - compte budgetaire des cotisations seul associé
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
        $idBudget = $this->paramService->getParametre('budget_cotisation'); // Budget
        $article->setBudget($this->budgetRepo->find($idBudget));
        
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

        // Si c'est une modification quand on analyse
        if ($remboursement->getArticle() != null) {
            $anc_article = $remboursement->getArticle();
            $article = new Article();
            $article->setCompteDebit($anc_article->getCompteCredit());        
            $article->setCompteCredit($anc_article->getCompteDebit());        
            $article->setLibelle("Annulation article N°".$anc_article->getId());
            $article->setCategorie($anc_article->getCategorie());
            $article->setAnalytique($anc_article->getAnalytique()); // Le congrégation rembourser
            $article->setMontant($anc_article->getMontant());
            $article->setDate($remboursement->getDate());
            $article->setPiece($anc_article->getPiece());
            $article->setIsFerme(true);
            $this->manager->persist($article);
        }

        $article = new Article();
        $article->setCompteDebit($compteRemboursement);        
        $article->setCompteCredit($remboursement->getTresorerie());        
        $article->setLibelle($label);
        $article->setCategorie($remboursement->getTresorerie()->getCodeJournal());
        $article->setMontant($remboursement->getMontant());
        $article->setDate($remboursement->getDate());
        $article->setPiece($remboursement->getReference());
        $article->setIsFerme(true); // No modifiable depuis journal

        $remboursement->setArticle($article);

        $this->manager->persist($remboursement);
        $this->manager->flush();    

        return $remboursement;
    }

    /*
    * Modification d'une remboursement éffectué (-Banque et Piece)
    */
    public function editRemboursement(Remboursement $remboursement)
    {
        $anc_article = $remboursement->getArticle();
        // Annulation de l'article précedent
        $article = new Article();
        $article->setCompteDebit($anc_article->getCompteCredit());        
        $article->setCompteCredit($anc_article->getCompteDebit());        
        $article->setLibelle("Annulation article N°".$anc_article->getId());
        $article->setCategorie($anc_article->getCategorie());
        $article->setAnalytique($anc_article->getAnalytique()); // Le congrégation rembourser
        $article->setMontant($anc_article->getMontant());
        $article->setDate($remboursement->getDate());
        $article->setPiece($anc_article->getPiece());
        $article->setIsFerme(true);

        // Nouveau écriture
        $label = $this->paramService->getParametre('label_prestation');    
        $label = str_replace('{a}', $remboursement->getExercice()->getAnnee(), $label);
        $label = str_replace('{c}', $remboursement->getAdherent()->getNom(), $label);

        $art_new = new Article();
        $art_new->setCompteDebit($anc_article->getCompteDebit()); // Compte de dette       
        $art_new->setCompteCredit($remboursement->getTresorerie());        
        $art_new->setLibelle($label);
        $art_new->setCategorie($remboursement->getTresorerie()->getCodeJournal());
        $art_new->setAnalytique($remboursement->getAdherent()->getCodeAnalytique()); // Le congrégation rembourser
        $art_new->setMontant($remboursement->getMontant());
        $art_new->setDate($remboursement->getDate());
        $art_new->setPiece($remboursement->getReference());
        $art_new->setIsFerme(true); 

        $remboursement->setArticle($art_new);

        $this->manager->persist($remboursement);
        $this->manager->persist($article);
        $this->manager->flush();    

        return $remboursement;
    }

    /*
    * IMPUTATION DE DECISION DE PRESTATION
    * - passe par le compte tiers 403 (fournisseurs - congregations) ainsi on a un compte Tiers
    * - charge de compte 606 (charge remboursement) - compte Analytique de la SOIN
    * - compte budgetaire des prestations associé 
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
            if ($prestation->getRembourse() != 0) {          
                $article = new Article();
                $article->setCompteDebit($compteRemboursement);        
                $article->setCompteCredit($compteRembDette); 
                $label = "Préstation bénéficiaire: ".$prestation->getPac()->getMatricule();
                $article->setLibelle($label);
                $article->setCategorie($journal);
                $article->setMontant($prestation->getRembourse());
                $article->setDate(new \DateTime());
                $piece = $prestation->getPac()->getMatricule() ."/". $this->session->get('exercice')->getAnnee();
                $article->setPiece('D'.$piece . "/" . $prestation->getDecompte()); // Le decompte de prestation
                $article->setIsFerme(true);
                // Compta ana - tier - budget
                $article->setTier($prestation->getAdherent()->getTier());
                $idBudget = $this->paramService->getParametre('budget_prestation');
                $article->setBudget($this->budgetRepo->find($idBudget));
                $compteAna = $this->analyticRepo->findOneBy(['code'=>$prestation->getDesignation()]);
                $article->setAnalytic($compteAna);

                $this->manager->persist($article);
            }
            $prestation->setDateDecision(new \DateTime());
        }       
        $this->manager->flush();       
    }

    /*
    * IMPUTATION DE VERSEMENT (REMISE) DE CHEQUE
    * - aucun compte Tiers
    * - aucun compte Analytique
    * - aucun poste Budgetaire 
    */
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