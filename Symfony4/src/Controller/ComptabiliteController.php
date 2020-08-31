<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Exercice;
use App\Form\CompteType;
use App\Form\ArticleType;
use Doctrine\ORM\EntityRepository;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\Form\FormError;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ComptabiliteController extends AbstractController
{
    /**
     * @Route("/comptabilite/journaux", name="comptabilite_select_journal")
     */
    public function selectJournal(CompteRepository $repositoryCompte)
    {
        return $this->render('comptabilite/selectJournal.html.twig', [
            'codes' => $repositoryCompte->findCodeJournaux()
        ]);
    }

    /**
     * @Route("/comptabilite/journal/{journal}", name="comptabilite_journal")
     */
    public function journal($journal = null, ArticleRepository $repositoryArticle, CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $codeJournaux = $repositoryCompte->findCodeJournaux();

        if ($journal == null) {
            $articles = $repositoryArticle->findJournal($exercice);
            $codeJournal = ['titre' => 'Génerale', 'codeJournal' => ''];
        } else {
            $codeJournal = $this->journal_exist($codeJournaux, $journal);
            if ($codeJournal) {
                $articles = $repositoryArticle->findJournal($exercice, $codeJournal['codeJournal']);
            } else {
                throw $this->createNotFoundException('Ce journal comptable n\'existe pas');
            }           
        }
        
        return $this->render('comptabilite/journal.html.twig', [
            'articles' => $articles,
            'journal'  => $codeJournal
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
        $choices = [];
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
                                    //->andWhere('c.classe != \'7-COMPTES DE PRODUITS\'')
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
                                    //->andWhere('c.classe != \'6-COMPTES DE CHARGES\'')
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
            if ($session->get('exercice')->check($date)) {
                // protect against negative solde
                $montant = $form->get('montant')->getData();
                $cCredit = $form->get('compteCredit')->getData();
                if ($cCredit->getIsTresor()) { // Trésorerie
                    $solde = $repositoryCompte->findSolde($cCredit);
                    if ($montant <= $solde) {
                        $manager->persist($article);
                        $manager->flush();
                        return $this->redirectToRoute('comptabilite_journal', ['journal' => $journal ]);
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

    /**
     * @Route("/comptabilite/grandlivre", name="comptabilite_livre")
     * @Route("/comptabilite/grandlivre/{poste}", name="comptabilite_livre_aux")
     */
    public function livre($poste = null, ArticleRepository $repositoryArticle, CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');

        if ($poste === null) {
            $donnees = $repositoryArticle->findGrandLivre($exercice);
        } else {
            $compte = $repositoryCompte->findOneByPoste($poste);
            if ($compte) {
                $articles = $repositoryArticle->findGrandLivreCompte($exercice, $compte);
                $donnees['compte'] = $compte;
                $donnees['articles'] = $articles;
            } else {
                throw $this->createNotFoundException("Le compte numéro $poste n'existe pas");
            }
        }
    
        $labelcomptes = $repositoryCompte->findPosteTitre();

        return $this->render('comptabilite/livre.html.twig', [
            'labelComptes' => $labelcomptes,
            'donnees' => $donnees,
            'isAux' => $poste != null,

        ]);
    }

    /**
     * @Route("/comptabilite/balance", name="comptabilite_balance")
     */
    public function balance(ArticleRepository $repositoryArticle, SessionInterface $session)
    {
        $exercice = $session->get('exercice');

        return $this->render('comptabilite/balance.html.twig', [
            'donnees' => $repositoryArticle->findBalance($exercice)
        ]);
    }

    /**
     * @Route("/comptabilite/plan", name="comptabilite_plan")
     */
    public function plan(CompteRepository $repositoryCompte)
    {
        return $this->render('comptabilite/plan.html.twig',[
            'classes' => [
                'bilan' => $repositoryCompte->findBilanGroupByClass(),
                'gestion' => $repositoryCompte->findGestionGroupByClass()
            ]
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
     * @Route("/comptabilite/plan/bilan", name="comptabilite_plan_bilan")
     */
    public function addCompteBilan(Request $request, EntityManagerInterface $manager)
    {
        $compte = new Compte();
        $compte->setIsTresor(false);
        $compte->setCategorie('COMPTES DE BILAN');
        $compte->setType(true); // Actif default
        $form = $this->createFormBuilder($compte)
                     ->add('classe', ChoiceType::class, [
                        'choices'  => [
                            '1-COMPTES DE CAPITAUX' => '1-COMPTES DE CAPITAUX',
                            '2-COMPTES D\'IMMOBILISATIONS' => '2-COMPTES D\'IMMOBILISATIONS',
                            '3-COMPTES DE STOCKS ET EN-COURS' => '3-COMPTES DE STOCKS ET EN-COURS',
                            '4-COMPTES DE TIERS' => '4-COMPTES DE TIERS',
                            '5-COMPTES FINANCIERS' => '5-COMPTES FINANCIERS',
                        ]
                     ])
                     ->add('poste')
                     ->add('titre')
                     ->add('type', CheckboxType::class, [ 'required' => false, 'label' => 'Actif / Passif' ])
                     ->add('note')
                     ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Verify class
            $class = $form->get('classe')->getData();
            $poste = $form->get('poste')->getData();
            if ($poste[0] == $class[0]) {
                $manager->persist($compte);
                $manager->flush();
                return $this->redirectToRoute('comptabilite_plan');
            } else {
                $form->get('poste')->addError(new FormError("Le numero de compte $poste n'appartient pas à la classe $class"));
            }   
        }

        return $this->render('comptabilite/compteForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comptabilite/plan/gestion", name="comptabilite_plan_gestion")
     */
    public function addCompteGestion(Request $request, EntityManagerInterface $manager)
    {
        $compte = new Compte();
        $compte->setIsTresor(false);
        $compte->setCategorie('COMPTES DE GESTION');
        $form = $this->createFormBuilder($compte)
                    ->add('classe', ChoiceType::class, [
                    'choices'  => [
                        '6-COMPTES DE CHARGES' => '6-COMPTES DE CHARGES',
                        '7-COMPTES DE PRODUITS' => '7-COMPTES DE PRODUITS'
                    ]
                    ])
                    ->add('poste')
                    ->add('titre')
                    ->add('note')
                    ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($compte->getClasse() === '6-COMPTES DE CHARGES') {
                $compte->setType(true);
            } else {
                $compte->setType(false);
            }
            // Verify class
            $class = $form->get('classe')->getData();
            $poste = $form->get('poste')->getData();
            if ($poste[0] == $class[0]) {
                $manager->persist($compte);
                $manager->flush();
                return $this->redirectToRoute('comptabilite_plan');
            } else {
                $form->get('poste')->addError(new FormError("Le numero de compte $poste n'appartient pas à la classe $class"));
            } 
        }

        return $this->render('comptabilite/compteForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comptabilite/bilan", name="comptabilite_bilan")
     */
    public function bilan(CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');      
        $donnees['actif'] = $repositoryCompte->findBilanActif($exercice);
        $donnees['passif'] = $repositoryCompte->findBilanPassif($exercice);
        return $this->render('comptabilite/bilan.html.twig', [
            'donnees' => $donnees,
        ]);
    }

    /**
     * @Route("/comptabilite/resultat", name="comptabilite_resultat")
     */
    public function resultat(CompteRepository $repositoryCompte, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $donnees['charge'] = $repositoryCompte->findGestionCharge($exercice);
        $donnees['produit'] = $repositoryCompte->findGestionProduit($exercice);
        return $this->render('comptabilite/resultat.html.twig', [
            'donnees' => $donnees,
        ]);
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
}
