<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Form\CompteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
        $compte = new Compte();
        $form = $this->createForm(CompteType::class, $compte);        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($compte);
            $manager->flush();
        }

        $comptes = $this->getDoctrine()->getRepository(Compte::class)->findAll();
        return $this->render('comptabilite/plan.html.twig',[
            'comptes' => $comptes,
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
