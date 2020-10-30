<?php

namespace App\Controller;

use App\Entity\Tier;
use App\Entity\Budget;
use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Exercice;
use App\Form\CompteType;
use App\Form\ArticleType;
use App\Entity\Analytique;
use App\Entity\ModelSaisie;
use App\Service\ImportExcel;
use App\Service\ConfigEtatFi;
use App\Repository\TierRepository;
use Doctrine\ORM\EntityRepository;
use App\Repository\BudgetRepository;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\FormError;
use App\Repository\ExerciceRepository;
use App\Repository\ParametreRepository;
use App\Repository\AnalytiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModelSaisieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ComptabiliteController extends AbstractController
{
    /**
     * @Route("/comptabilite/journal", name="comptabilite_view_journal")
     */
    public function viewJournal(Request $request, ParametreRepository $parametreRepo, CompteRepository $repositoryCompte, ArticleRepository $repositoryArticle, SessionInterface $session)
    {
        $exercice = $session->get('exercice');

        $code = $request->query->get('code');
        $debut = $request->query->get('debut');
        $fin = $request->query->get('fin');

        // Verifier si les deux dates sont compris sinon, l'exercice tous entier
        $dateDebut = $exercice->getDateDebut(); 
        $dateFin = $exercice->getDateFin();
        if ($debut) {
            $d = \DateTime::createFromFormat('d/m/Y', $debut);
            if ($exercice->check($d))
                $dateDebut = $d;
        }
        if ($fin) {
            $d = \DateTime::createFromFormat('d/m/Y', $fin);
            if ($exercice->check($d) && $d > $dateDebut)
                $dateFin = $d;
        }

        $articles = $repositoryArticle->findJournal($code, $dateDebut, $dateFin);
        return $this->render('comptabilite/journal.html.twig', [
            'articles' => $articles,
            'code' => $repositoryCompte->findCodeJournaux($code)[0],
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
            'mutuelle' => $parametreRepo->findDonneesMutuelle()
        ]);
    }

    // SAISIE D'UNE ECRITURE COMPTABLE
    /**
     * @Route("/comptabilite/saisie", name="comptabilite_saisie_standard")
     * @Route("/comptabilite/saisie/edit/{id}", name="comptabilite_saisie_edit")
     */
    public function saisieStandard(Article $article = null, Request $request, CompteRepository $repositoryCompte, EntityManagerInterface $manager, SessionInterface $session)
    {
        // Les codes journaux
        $codes = $repositoryCompte->findCodeJournaux();
        foreach ($codes as $code)
            $cCodes[$code['titre'].' ('.$code['codeJournal'].')'] = $code['codeJournal'];
        
        $edit = true;
        // Article 
        if ($article === null) {
            $article = new Article();
            $article->setCategorie('OD');
            $edit = false;
        }

        $form = $this->createFormBuilder($article)
                    ->add('categorie', ChoiceType::class, [
                        'choices' => $cCodes
                    ]) 
                    ->add('date', DateType::class, [
                        'data' => new \DateTime(),
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                        'attr' => ['class' => 'datepicker','autocomplete' => 'off'],
                        'html5' => false,
                    ])
                    ->add('montant', MoneyType::class, [
                        'currency' => 'MGA',
                        'grouping' => true
                    ])
                    ->add('libelle')
                    ->add('piece')           
                    ->add('compteDebit', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('length(c.poste) = 6')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                            return $c->getPoste().' | '.$c->getTitre();
                        },
                    ])
                    ->add('compteCredit', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('length(c.poste) = 6')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                            return $c->getPoste().' | '.$c->getTitre();
                        },
                    ])
                    ->add('analytic', EntityType::class, [
                        'class' => Analytique::class,
                        'required' => false,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('a')
                                    ->andWhere('a.isServiceSante = false');
                        },
                        'choice_label' => function ($a) {
                            return $a->getLibelle().' ('.$a->getCode().')';
                        }
                    ])
                    ->add('tier', EntityType::class, [
                        'class' => Tier::class,
                        'required' => false,
                        'choice_label' => function ($a) {
                            return $a->getLibelle().' ('.$a->getCode().')';
                        }
                    ])
                    ->add('budget', EntityType::class, [
                        'class' => Budget::class,
                        'required' => false,
                        'choice_label' => function ($a) {
                            return $a->getLibelle().' ('.$a->getCode().')';
                        }
                    ])
                    ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
            $exercice = $session->get('exercice');
            if ($exercice->check($date)) {
                // protect against negative solde
                $montant = $form->get('montant')->getData();
                $cCredit = $form->get('compteCredit')->getData();
                if ($cCredit->getIsTresor()) { // Trésorerie
                    $solde = $repositoryCompte->findSoldes([$cCredit->getPoste()], $exercice);
                    if ($montant <= $solde) {
                        $manager->persist($article);

                        $manager->flush(); 
                        $message = ($edit)?'La modification du mouvement de numéro '.$article->getId().' effectué':'Le numéro de mouvement de la pièce créé est '.$article->getId();
                        $this->addFlash('success', $message);
                        return $this->redirectToRoute('comptabilite_saisie_standard');
                    } else {
                        $form->get('montant')->addError(new FormError("Le solde negatif est interdit pour les trésoreries. Solde actuelle $solde"));
                    }
                } else { // Operation divers
                    $manager->persist($article);
                    $manager->flush();
                    $message = ($edit)?'La modification du mouvement de numéro '.$article->getId().' effectué':'Le numéro de mouvement de la pièce créé est '.$article->getId();
                    $this->addFlash('success', $message);
                    return $this->redirectToRoute('comptabilite_saisie_standard');
                }
            } else {
                $form->get('date')->addError(new FormError("La date ".$date->format('d/m/Y')." n'appartient pas à l'exercice courant"));
            }
        }

        return $this->render('comptabilite/saisieStandard.html.twig', [
            'form' => $form->createView(),
            'edit' => $article->getId()
        ]);
    }

    /**
     * @Route("/comptabilite/saisie/model/{id}", name="comptabilite_saisie_model")
     */
    public function saisieModel(ModelSaisie $model,Request $request, CompteRepository $repositoryCompte, EntityManagerInterface $manager, SessionInterface $session)
    {
        // Les codes journaux
        $codes = $repositoryCompte->findCodeJournaux();
        foreach ($codes as $code)
            $cCodes[$code['titre'].' ('.$code['codeJournal'].')'] = $code['codeJournal'];
        
        // Article 
        $article = new Article();
        $article->setCategorie($model->getJournal());
        $article->setCompteDebit($model->getDebit());
        $article->setCompteCredit($model->getCredit());
        $article->setAnalytic($model->getAnalytic());
        $article->setTier($model->getTier());
        $article->setBudget($model->getBudget());

        $form = $this->createFormBuilder($article)
                    ->add('categorie', ChoiceType::class, [
                        'choices' => $cCodes
                    ]) 
                    ->add('date', DateType::class, [
                        'data' => new \DateTime(),
                        'widget' => 'single_text',
                        'format' => 'dd/MM/yyyy',
                        'attr' => ['class' => 'datepicker','autocomplete' => 'off'],
                        'html5' => false,
                    ])
                    ->add('montant', MoneyType::class, [
                        'currency' => 'MGA',
                        'grouping' => true
                    ])
                    ->add('libelle')
                    ->add('piece')           
                    ->add('compteDebit', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('length(c.poste) = 6')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                            return $c->getPoste().' | '.$c->getTitre();
                        },
                    ])
                    ->add('compteCredit', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('length(c.poste) = 6')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                            return $c->getPoste().' | '.$c->getTitre();
                        },
                    ])
                    ->add('analytic', EntityType::class, [
                        'class' => Analytique::class,
                        'required' => false,
                        'choice_label' => function ($a) {
                            return $a->getLibelle().' ('.$a->getCode().')';
                        }
                    ])
                    ->add('tier', EntityType::class, [
                        'class' => Tier::class,
                        'required' => false,
                        'choice_label' => function ($a) {
                            return $a->getLibelle().' ('.$a->getCode().')';
                        }
                    ])
                    ->add('budget', EntityType::class, [
                        'class' => Budget::class,
                        'required' => false,
                        'choice_label' => function ($a) {
                            return $a->getLibelle().' ('.$a->getCode().')';
                        }
                    ])
                    ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
            $exercice = $session->get('exercice');
            if ($exercice->check($date)) {
                // protect against negative solde
                $montant = $form->get('montant')->getData();
                $cCredit = $form->get('compteCredit')->getData();
                if ($cCredit->getIsTresor()) { // Trésorerie
                    $solde = $repositoryCompte->findSoldes([$cCredit->getPoste()], $exercice);
                    if ($montant <= $solde) {
                        $manager->persist($article);
                        $manager->flush(); 
                        $message ='Le numéro de mouvement de la pièce créé est '.$article->getId();
                        $this->addFlash('success', $message);
                        return $this->redirectToRoute('comptabilite_saisie_model');
                    } else {
                        $form->get('montant')->addError(new FormError("Le solde negatif est interdit pour les trésoreries. Solde actuelle $solde"));
                    }
                } else { // Operation divers
                    $manager->persist($article);
                    $manager->flush();
                    $message = 'Le numéro de mouvement de la pièce créé est '.$article->getId();
                    $this->addFlash('success', $message);
                    return $this->redirectToRoute('comptabilite_saisie_model');
                }
            } else {
                $form->get('date')->addError(new FormError("La date ".$date->format('d/m/Y')." n'appartient pas à l'exercice courant"));
            }
        }

        return $this->render('comptabilite/saisieModel.html.twig', [
            'form' => $form->createView(),
            'model' => $model
        ]);
    }    

    /**
     * @Route("/comptabilite/model/saisie", name="comptabilite_model_saisie")
     */
    public function modelSaisie(Request $request, CompteRepository $repositoryCompte,EntityManagerInterface $manager, ModelSaisieRepository $repo)
    {
        $journaux = $repositoryCompte->findCodeJournaux();
        foreach ($journaux as $code) {
            $codes[$code['titre'].' ('.$code['codeJournal'].')'] = $code['codeJournal'];
        }
        $model = new ModelSaisie();
        $form = $this->createFormBuilder($model)
                ->add('nom')
                ->add('type', ChoiceType::class, [
                    'choices' => ['Entré'=>'E', 'Sortie'=>'S','Entré / Sortie'=>'ES', 'Trésorerie'=>'T']
                ])  
                ->add('journal', ChoiceType::class, [
                    'choices' => $codes
                ])                    
                ->add('debit', EntityType::class, [
                    'class' => Compte::class,
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->andWhere('length(c.poste) = 6')
                                ->orderBy('c.poste', 'ASC');
                    },
                    'choice_label' => function ($c) {
                        return $c->getPoste().' | '.$c->getTitre();
                    },
                ])
                ->add('credit', EntityType::class, [
                    'class' => Compte::class,
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->andWhere('length(c.poste) = 6')
                                ->orderBy('c.poste', 'ASC');
                    },
                    'choice_label' => function ($c) {
                        return $c->getPoste().' | '.$c->getTitre();
                    },
                ])
                ->add('analytic', EntityType::class, [
                    'class' => Analytique::class,
                    'required' => false,
                    'choice_label' => function ($a) {
                        return $a->getLibelle().' ('.$a->getCode().')';
                    }
                ])
                ->add('tier', EntityType::class, [
                    'class' => Tier::class,
                    'required' => false,
                    'choice_label' => function ($a) {
                        return $a->getLibelle().' ('.$a->getCode().')';
                    }
                ])
                ->add('budget', EntityType::class, [
                    'class' => Budget::class,
                    'required' => false,
                    'choice_label' => function ($a) {
                        return $a->getLibelle().' ('.$a->getCode().')';
                    }
                ])
                ->getForm();   
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dump($model);
            $manager->persist($model);
            $manager->flush();
            return $this->redirectToRoute('comptabilite_model_saisie');
        }

        return $this->render('comptabilite/modelSaisie.html.twig', [
            'models' => $repo->findAll(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comptabilite/model/saisie/del/{id}", name="comptabilite_model_saisie_del")
     */
    public function deleteModeleSaisie(ModelSaisie $model,EntityManagerInterface $manager)
    {
        $manager->remove($model);
        $manager->flush();
        return $this->redirectToRoute('comptabilite_model_saisie');
    }

    /**
     * @Route("/comptabilite/grandlivre", name="comptabilite_livre")
     */
    public function livre(Request $request,ParametreRepository $parametreRepo, ArticleRepository $repositoryArticle, CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $poste = $request->query->get('poste');
        $debut = $request->query->get('debut');
        $fin = $request->query->get('fin');

        // Verifier si les deux dates sont compris sinon, l'exercice tous entier
        $dateDebut = $exercice->getDateDebut(); 
        $dateFin = $exercice->getDateFin();
        if ($debut) {
            $d = \DateTime::createFromFormat('d/m/Y', $debut);
            if ($exercice->check($d)) {
                $dateDebut = $d;
            }
        }

        if ($fin) {
            $d = \DateTime::createFromFormat('d/m/Y', $fin);
            if ($exercice->check($d) && $d > $dateDebut) {
                $dateFin = $d;
            }
        }

        if ($poste == "") {
            $donnees = $repositoryArticle->findGrandLivre($dateDebut, $dateFin);
            $subtitle = "Complet";
        } else {
            $compte = $repositoryCompte->findOneByPoste($poste);
            if ($compte)
                $donnees = $repositoryArticle->findGrandLivreCompte($dateDebut, $dateFin, $compte);
            else 
                throw $this->createNotFoundException("Le compte numéro $poste n'existe pas");
            $subtitle = $compte->getTitre();
        }
    
        return $this->render('comptabilite/livre.html.twig', [
            'donnees' => $donnees,
            'poste' => $poste,
            'subtitle' => $subtitle,
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
            'mutuelle' => $parametreRepo->findDonneesMutuelle()
        ]);
    }

    /**
     * @Route("/comptabilite/balance", name="comptabilite_balance")
     */
    public function balance(Request $request,ParametreRepository $parametreRepo, ArticleRepository $repositoryArticle, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $fin = $request->query->get('fin');

        $dateDebut = $exercice->getDateDebut(); 
        $dateFin = $exercice->getDateFin();
        if ($fin) {
            $d = \DateTime::createFromFormat('d/m/Y', $fin);
            if ($exercice->check($d) && $d > $dateDebut) {
                $dateFin = $d;
            }
        }

        return $this->render('comptabilite/balance.html.twig', [
            'donnees' => $repositoryArticle->findBalance($dateDebut, $dateFin),
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin],
            'mutuelle' => $parametreRepo->findDonneesMutuelle()
        ]);
    }

    /**
     * @Route("/comptabilite/plan/generale", name="comptabilite_plan")
     */
    public function plan(CompteRepository $repositoryCompte, Request $request, EntityManagerInterface $manager)
    {
        $compte = new Compte();     
        $form = $this->createFormBuilder($compte)   
                     ->add('poste', TextType::class, [
                        'constraints' => [
                            new Length([
                                'max' => 6
                            ])
                        ]
                     ])
                     ->add('titre')
                     ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // verifier si le rubrique existe
            $rub = substr($compte->getPoste(), 0, 2);
            if ($repositoryCompte->findByPoste($rub)) {
                $manager->persist($compte);
                $manager->flush();
            } else {
                $form->get('poste')->addError(new FormError("Le rubrique $rub n'existe pas dans le PCG, veuillez changer le numéro"));
            }      
        } 
        return $this->render('comptabilite/plan.html.twig',[
            'comptes' =>  $repositoryCompte->findComptes(),
            'form' => $form->createView()
        ]);
    }   

    /**
     * @Route("/comptabilite/plan/budgetaire", name="comptabilite_plan_budgetaire")
     */
    public function planBudgetaire(BudgetRepository $repo, SessionInterface $session)
    {
        $exercice = $session->get('exercice');   
        return $this->render('comptabilite/budget.html.twig', [
            'budgets' => json_encode($repo->findExercice($exercice))
        ]);
    }

    /**
     * @Route("/comptabilite/plan/budgetaire/add", name="add_budget")
     */
    public function addBudget(Request $request, ExerciceRepository $repoExercice, EntityManagerInterface $manager, SessionInterface $session)
    {
        $idE = $session->get('exercice')->getId(); 
        $exercice = $repoExercice->find($idE);
        $budget = new Budget($request->request->get('code'),$request->request->get('libelle'),$request->request->get('prevision'),$request->request->get('input'));
        $budget->setExercice($exercice);
        $manager->persist($budget);
        $manager->flush();
        return new JsonResponse([
            'hasError' => false,
            'message' => "Budget ajouter avec success",
            'budget' => $budget->getAsArray()
        ]);
    }

    /**
     * @Route("/comptabilite/plan/tiers", name="comptabilite_plan_tiers")
     */
    public function planTiers(Request $request, TierRepository $repo, CompteRepository $compteRepo,EntityManagerInterface $manager)
    {
        if ($request->isMethod('post')) {
            $code = $request->request->get('code');
            $intitule = $request->request->get('libelle');
            $adresse = $request->request->get('adresse');
            $contact = $request->request->get('contact');
            $type = $request->request->get('type');
            $poste = $compteRepo->findOneByPoste($request->request->get('poste'));

            $tier = new Tier();
            $tier->setCode($code);
            $tier->setLibelle($intitule);
            $tier->setAdresse($adresse);
            $tier->setContact($contact);
            $tier->setType($type);
            $tier->setCompte($poste);
            $manager->persist($tier);
            $manager->flush();
            return new JsonResponse([
                'hasError' => false,
                'message' => "Budget ajouter avec success",
                'tier' => $tier->getAsArray()
            ]);
        }

        return $this->render('comptabilite/tiers.html.twig', [
            'tiers' => json_encode($repo->findResult()),
            'comptesTiers' => $compteRepo->findPosteTitre("4%")
        ]);
    }

    /**
     * @Route("/comptabilite/plan/analytics", name="comptabilite_plan_analytics")
     */
    public function planAnalytics(Request $request, AnalytiqueRepository $repo,EntityManagerInterface $manager,SessionInterface $session)
    {
        if ($request->isMethod('post')) {
            $ana = new Analytique($request->request->get('code'),$request->request->get('libelle'));
            $manager->persist($ana);
            $manager->flush();
            return new JsonResponse([
                'hasError' => false,
                'message' => "Analytique ajouter avec success",
                'analytique' => ['id'=>$ana->getId(),'code'=>$ana->getCode(),'libelle'=>$ana->getLibelle()]
            ]);
        }

        return $this->render('comptabilite/ana.html.twig', [
            'analytics' => json_encode($repo->findAnalytics($session->get('exercice'))),
        ]);
    }

    /**
     * @Route("/comptabilite/bilan", name="comptabilite_bilan")
     */
    public function bilan(ConfigEtatFi $etatFi,ParametreRepository $parametreRepo, CompteRepository $repo, SessionInterface $session)
    {
        $exercice = $session->get('exercice'); 
        $actifNonCourant = $etatFi->actifNonCourant();  
        $actifCourant = $etatFi->actifCourant();  
        $capitaux = $etatFi->capitauxPropres();
        $passifsNonCourants = $etatFi->passifNonCourant();
        $passifsCourants = $etatFi->passifCourant();
        
        return $this->render('comptabilite/bilanExercice.html.twig', [
            'actifsNonCourants' => $this->getBilan($exercice, $repo, $actifNonCourant),
            'actifsCourants' => $this->getBilan($exercice, $repo, $actifCourant),
            'capitaux' => $this->getBilan($exercice, $repo, $capitaux),
            'passifsNonCourants' => $this->getBilan($exercice, $repo, $passifsNonCourants),
            'passifsCourants' => $this->getBilan($exercice, $repo, $passifsCourants),
            'mutuelle' => $parametreRepo->findDonneesMutuelle()
        ]);
    }

    /** 
     * Convert bilan poste into solde
     */
    private function getBilan($exercice, $repo, $groupes)
    {
        $anc = [];
        foreach ($groupes as $rubrique) {
            if (count($rubrique) == 4) {
                $anc[] = [ $rubrique[0], $rubrique[1], $repo->findSoldes($rubrique[2], $exercice), $repo->findSoldes($rubrique[3], $exercice)];
            } else {
                $anc[] = $rubrique;
            }
        }
        return $anc;
    }

    /**
     * @Route("/comptabilite/resultat", name="comptabilite_resultat")
     */
    public function resultat(ConfigEtatFi $etatFi,ParametreRepository $parametreRepo, CompteRepository $repo, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $chiffreAffaireNet = $etatFi->chiffreAffaireNet();  
        $productionExploitation = $etatFi->productionExploitation();
        $chargesExploitation = $etatFi->chargeExploitation();
        $op = $etatFi->operationEnCommmun();
        $pFinanciers = $etatFi->productionsFinanciers();
        $cFinanciers = $etatFi->chargesFinanciers();
        $pException = $etatFi->produitExceptionnel();
        $cException = $etatFi->chargeExceptionnel();
        $impots = $etatFi->impots();

        return $this->render('comptabilite/resultat.html.twig', [
            'chiffreAffaireNet' => $this->getResultat($exercice, $repo, $chiffreAffaireNet),
            'productionExploitation' => $this->getResultat($exercice, $repo, $productionExploitation),
            'chargesExploitation' => $this->getResultat($exercice, $repo, $chargesExploitation),
            'operationCommun' => $this->getResultat($exercice, $repo, $op),
            'produitsFinanciers' => $this->getResultat($exercice, $repo, $pFinanciers),
            'chargesFinanciers' => $this->getResultat($exercice, $repo, $cFinanciers),
            'produitsExceptionnels' => $this->getResultat($exercice, $repo, $pException),
            'chargesExceptionnels' => $this->getResultat($exercice, $repo, $cException),
            'impots' => $this->getResultat($exercice, $repo, $impots),
            'mutuelle' => $parametreRepo->findDonneesMutuelle()
        ]);
    }

    private function getResultat($exercice, $repo, $groupes)
    {
        $anc = [];
        foreach ($groupes as $rubrique) {
            if (count($rubrique) == 3) {
                $anc[] = [ $rubrique[0], $rubrique[1], $repo->findSoldes($rubrique[2], $exercice)];
            } else {
                $anc[] = $rubrique;
            }
        }
        return $anc;
    }

    private function journal_exist($codeJournaux, $code)
    {
        foreach ($codeJournaux as $codeJournal) {
            if ($codeJournal['codeJournal'] == $code) {
                return $codeJournal;
            }
        }
        return false;
    }

    /**
     * @Route("/comptabilite/plan/importer", name="import_plan_comptable")
     */
    public function importPlanComptable(Request $request, ImportExcel $importService)
    {
        $form = $this->createFormBuilder()
                    ->add('file', FileType::class, [
                        'mapped' => false,
                        'required' => true,
                    ])
                    ->add('save', SubmitType::class, ['label' => 'Importer xlsx'])
                    ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $xlsxFile = $form->get('file')->getData();
            if ($xlsxFile) {
                $output = $importService->importPlanComptable($xlsxFile);  
                if ($output['hasError'] === false) {
                    return $this->redirectToRoute('comptabilite_plan'); 
                } else {
                    foreach ($output['ErrorMessages'] as $message) {
                        $form->get('file')->addError(new FormError($message));
                    }
                }             
            }
            
        }
        
        return $this->render('comptabilite/importPlanComptable.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function renderModalJournal(ModelSaisieRepository $modelRepo, CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');

        $start    = ($exercice->getDateDebut())->modify('first day of this month');
        $x = \DateTimeImmutable::createFromMutable($exercice->getDateFin());   
        $end      = $x->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $periods   = new \DatePeriod($start, $interval, $end);

        return $this->render('comptabilite/modalJournal.html.twig', [
            'codes' => $repositoryCompte->findCodeJournaux(),
            'models' => $modelRepo->findAll(),
            'periodes' => $periods,
            'labelComptes' => $repositoryCompte->findPosteTitre()
        ]);
    }
}
