<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Form\CompteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ComptabiliteController extends AbstractController
{
    /**
     * @Route("/comptabilite/journal", name="comptabilite_journal")
     */
    public function journal()
    {
        return $this->render('comptabilite/selectJournal.html.twig');
    }

    /**
     * @Route("/comptabilite/journal/cotisation", name="comptabilite_journal_cotisation")
     */
    public function journalCotisation()
    {
        $articlesCotisations = $this->getDoctrine()->getRepository(Article::class)->findCotisations();
        return $this->render('comptabilite/journalCotisation.html.twig', [
            'articles' => $articlesCotisations,
        ]);
    }

    /**
     * @Route("/comptabilite/journal/reboursement", name="comptabilite_journal_reboursement")
     */
    public function journalRemboursement()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findRemboursements();
        return $this->render('comptabilite/journalRemboursement.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/comptabilite/livre", name="comptabilite_livre")
     */
    public function livre()
    {
        return $this->render('comptabilite/livre.html.twig');
    }

    /**
     * @Route("/comptabilite/balance", name="comptabilite_balance")
     */
    public function balance()
    {
        return $this->render('comptabilite/balance.html.twig');
    }

    /**
     * @Route("/comptabilite/plan", name="comptabilite_plan")
     */
    public function plan(Request $request)
    {
        $classesBilan = $this->getDoctrine()->getRepository(Compte::class)->findBilanGroupByClass();
        $classesGestion = $this->getDoctrine()->getRepository(Compte::class)->findGestionGroupByClass();
        return $this->render('comptabilite/plan.html.twig',[
            'classes' => [
                'bilan' => $classesBilan,
                'gestion' => $classesGestion
            ]
        ]);
    }

    /**
     * @Route("/comptabilite/plan/bilan", name="comptabilite_plan_bilan")
     */
    public function addCompteBilan(Request $request)
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
            $manager = $this->getDoctrine()->getManager();
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
    public function addCompteGestion(Request $request)
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

            $manager = $this->getDoctrine()->getManager();
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
    public function bilan()
    {
        return $this->render('comptabilite/bilan.html.twig');
    }

    /**
     * @Route("/comptabilite/resultat", name="comptabilite_resultat")
     */
    public function resultat()
    {
        return $this->render('comptabilite/resultat.html.twig');
    }
}
