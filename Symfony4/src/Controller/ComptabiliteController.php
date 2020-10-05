<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Exercice;
use App\Form\CompteType;
use App\Form\ArticleType;
use App\Service\ImportExcel;
use App\Service\ConfigEtatFi;
use Doctrine\ORM\EntityRepository;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\FormError;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
    public function viewJournal(Request $request, CompteRepository $repositoryCompte, ArticleRepository $repositoryArticle, SessionInterface $session)
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
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin]
        ]);
    }

    /**
     * @Route("/comptabilite/journal/{journal}/saisie", name="comptabilite_saisie")
     * @Route("/comptabilite/journal/{journal}/modifier/{id}", name="comptabilite_modifier_article")
     */
    public function saisie($journal, Article $article = null, Request $request, ParametreRepository $repositoryParametre, CompteRepository $repositoryCompte, EntityManagerInterface $manager, SessionInterface $session)
    {
        if (!$article) {
            $article = new Article();
            $article->setCompteDebit($repositoryCompte->findOneBy(['codeJournal' => $journal]))
                    ->setCompteCredit($repositoryCompte->findOneBy(['codeJournal' => $journal]))
                    ->setCategorie($journal);
        }    
        $plan_analytiques = $repositoryParametre->findOneBy(['nom' => 'plan_analytique'])->getList();
        $choices['NULL'] = null;
        foreach ($plan_analytiques as $key => $value) {
            $choices[$key.' | '.$value] = $key;
        } // reverse key value
        $form = $this->createFormBuilder($article)
                    ->add('date', DateType::class, [
                        'data' => new \DateTime()
                    ])
                    ->add('montant', NumberType::class)
                    ->add('libelle')
                    ->add('piece')
                    ->add('analytique', ChoiceType::class, [
                        'choices' => $choices
                    ])            
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
                    ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Verifier la date
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
                        return $this->redirectToRoute('comptabilite_view_journal', ['journal' => $journal ]);
                    } else {
                        $form->get('montant')->addError(new FormError("Le solde negatif est interdit pour les trésoreries. Solde actuelle $solde"));
                    }
                }
                else { // Operation divers
                    $manager->persist($article);
                    $manager->flush();
                    return $this->redirectToRoute('comptabilite_journal', ['journal' => $journal ]);
                }
            } else {
                $form->get('date')->addError(new FormError("La date ".$date->format('d/m/Y')." n'appartient pas à l'exercice courant"));
            }
        }

        return $this->render('comptabilite/saisie.html.twig', [
            'form' => $form->createView(),
            'codeJournal' => $journal,
            'editMode' => $article->getId() !== null,
        ]);
    }

    // SAISIE D'UNE ECRITURE COMPTABLE
    /**
     * @Route("/comptabilite/saisie", name="comptabilite_saisie_standard")
     */
    public function saisieStandard(Request $request, ParametreRepository $repositoryParametre, CompteRepository $repositoryCompte, EntityManagerInterface $manager, SessionInterface $session)
    {
        // Affichage du plan analytique
        $plan_analytiques = $repositoryParametre->findOneBy(['nom' => 'plan_analytique'])->getList();
        $choices['...'] = null;
        foreach ($plan_analytiques as $key => $value) {
            $choices[$value.' ('.$key.')'] = $key;
        } // reverse key value
        // Les codes journaux
        $codes = $repositoryCompte->findCodeJournaux();
        foreach ($codes as $code) {
            $cCodes[$code['titre'].' ('.$code['codeJournal'].')'] = $code['codeJournal'];
        }

        // Article 
        $article = new Article();
        $article->setCategorie('OD');
      
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
                    ->add('analytique', ChoiceType::class, [
                        'choices' => $choices
                    ])            
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
                    } else {
                        $form->get('montant')->addError(new FormError("Le solde negatif est interdit pour les trésoreries. Solde actuelle $solde"));
                    }
                } else { // Operation divers
                    $manager->persist($article);
                    $manager->flush();
                    return $this->redirectToRoute('comptabilite_journal', ['journal' => $journal ]);
                }
            } else {
                $form->get('date')->addError(new FormError("La date ".$date->format('d/m/Y')." n'appartient pas à l'exercice courant"));
            }
        }
        return $this->render('comptabilite/saisieStandard.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comptabilite/grandlivre", name="comptabilite_livre")
     */
    public function livre(Request $request, ArticleRepository $repositoryArticle, CompteRepository $repositoryCompte, SessionInterface $session)
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
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin]
        ]);
    }

    /**
     * @Route("/comptabilite/balance", name="comptabilite_balance")
     */
    public function balance(Request $request, ArticleRepository $repositoryArticle, SessionInterface $session)
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
            'periode' => ['debut' => $dateDebut, 'fin' => $dateFin]
        ]);
    }

    /**
     * @Route("/comptabilite/plan", name="comptabilite_plan")
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
     * @Route("/comptabilite/plan/analytique", name="comptabilite_plan_analytique")
     */
    public function planAnalytique(Request $request, ParametreRepository $repositoryParametre, EntityManagerInterface $manager)
    {
        $parametre = $repositoryParametre->findOneBy(['nom' => 'plan_analytique']);
        $data['plan_analytique'] = json_encode($parametre->getList());
        $form = $this->createFormBuilder($data)
                    ->add('plan_analytique', TextareaType::class)
                    ->getForm()
        ;   
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {   
            $parametre->setList(json_decode($form->get('plan_analytique')->getData(), true));
            $manager->flush();
        }

        return $this->render('comptabilite/analytique.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comptabilite/bilan", name="comptabilite_bilan")
     */
    public function bilan(ConfigEtatFi $etatFi, CompteRepository $repo, SessionInterface $session)
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
            'passifsCourants' => $this->getBilan($exercice, $repo, $passifsCourants)
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
    public function resultat(ConfigEtatFi $etatFi, CompteRepository $repo, SessionInterface $session)
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
            'impots' => $this->getResultat($exercice, $repo, $impots)
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

    public function renderModalJournal(CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');

        $start    = ($exercice->getDateDebut())->modify('first day of this month');
        $x = \DateTimeImmutable::createFromMutable($exercice->getDateFin());   
        $end      = $x->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $periods   = new \DatePeriod($start, $interval, $end);

        return $this->render('comptabilite/modalJournal.html.twig', [
            'codes' => $repositoryCompte->findCodeJournaux(),
            'periodes' => $periods,
            'labelComptes' => $repositoryCompte->findPosteTitre()
        ]);
    }
}
