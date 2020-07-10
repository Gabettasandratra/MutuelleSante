<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Exercice;
use App\Form\CompteType;
use App\Form\ArticleType;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ComptabiliteController extends AbstractController
{
    /**
     * @Route("/comptabilite/journal", name="comptabilite_select_journal")
     */
    public function selectJournal()
    {
        return $this->render('comptabilite/selectJournal.html.twig');
    }

    /**
     * @Route("/comptabilite/journal/{journal}", name="comptabilite_journal")
     */
    public function journal($journal, ArticleRepository $repositoryArticle)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

        if ($journal == 'cotisation') {
            $articles = $repositoryArticle->findJournal($exercice, 'Cotisation');
        } else if ($journal == 'remboursement') {
            $articles = $repositoryArticle->findJournal($exercice, 'Remboursement');
        } else if ($journal == 'divers') {
            $articles = $repositoryArticle->findJournal($exercice, 'Divers');
        } else {
            throw $this->createNotFoundException('Ce journal comptable n\'existe pas');
        }
        
        return $this->render('comptabilite/journal.html.twig', [
            'articles' => $articles,
            'journal'  => $journal
        ]);
    }

    /**
     * @Route("/comptabilite/journal/{journal}/saisie", name="comptabilite_saisie")
     * @Route("/comptabilite/journal/{journal}/modifier/{id}", name="comptabilite_modifier_article")
     */
    public function saisie($journal, Article $article = null, Request $request, EntityManagerInterface $manager)
    {
        if (!$article) {
            $article = new Article();
        }
    
        $article->setCategorie('Divers');
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('comptabilite_journal', ['journal' => 'divers']);
        }

        return $this->render('comptabilite/saisie.html.twig', [
            'form' => $form->createView(),
            'editMode' => $article->getId() !== null,
        ]);
    }

    /**
     * @Route("/comptabilite/grandlivre", name="comptabilite_livre")
     * @Route("/comptabilite/grandlivre/{poste}", name="comptabilite_livre_aux")
     */
    public function livre($poste = null, ArticleRepository $repositoryArticle, CompteRepository $repositoryCompte)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

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
    public function balance(ArticleRepository $repositoryArticle)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

        return $this->render('comptabilite/balance.html.twig', [
            'donnees' => $repositoryArticle->findBalance($exercice)
        ]);
    }

    /**
     * @Route("/comptabilite/plan", name="comptabilite_plan")
     */
    public function plan(Request $request, CompteRepository $repositoryCompte)
    {
        return $this->render('comptabilite/plan.html.twig',[
            'classes' => [
                'bilan' => $repositoryCompte->findBilanGroupByClass(),
                'gestion' => $repositoryCompte->findGestionGroupByClass()
            ]
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
            $manager->persist($compte);
            $manager->flush();
            return $this->redirectToRoute('comptabilite_plan');
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

            $manager->persist($compte);
            $manager->flush();
            return $this->redirectToRoute('comptabilite_plan');
        }

        return $this->render('comptabilite/compteForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comptabilite/bilan", name="comptabilite_bilan")
     */
    public function bilan(CompteRepository $repositoryCompte)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

        $donnees['actif'] = $repositoryCompte->findBilanActif($exercice);
        $donnees['passif'] = $repositoryCompte->findBilanPassif($exercice);
        return $this->render('comptabilite/bilan.html.twig', [
            'donnees' => $donnees,
        ]);
    }

    /**
     * @Route("/comptabilite/resultat", name="comptabilite_resultat")
     */
    public function resultat(CompteRepository $repositoryCompte)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

        $donnees['charge'] = $repositoryCompte->findGestionCharge($exercice);
        $donnees['produit'] = $repositoryCompte->findGestionProduit($exercice);
        return $this->render('comptabilite/resultat.html.twig', [
            'donnees' => $donnees,
        ]);
    }
}
