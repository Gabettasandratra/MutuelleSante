<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ComptabiliteController extends AbstractController
{
    /**
     * @Route("/comptabilite", name="comptabilite")
     */
    public function index()
    {
        return $this->render('comptabilite/journal.html.twig');
    }

    /**
     * @Route("/comptabilite/journal", name="comptabilite_journal")
     */
    public function journal()
    {
        return $this->render('comptabilite/journal.html.twig');
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
    public function plan()
    {
        return $this->render('comptabilite/plan.html.twig');
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
