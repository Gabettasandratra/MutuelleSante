<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Adherent;
use App\Entity\Pac;

class PrestationController extends AbstractController
{
    /**
     * @Route("/prestation", name="prestation")
     */
    public function index()
    {
        $adherents = $this->getDoctrine()
                          ->getRepository(Adherent::class)
                          ->findAll();
        return $this->render('prestation/index.html.twig', [
            'adherents' => $adherents
        ]);
    }

    /**
     * @Route("/prestation/list", name="prestation_list")
     */
    public function list()
    {
        $adherents = $this->getDoctrine()
                          ->getRepository(Adherent::class)
                          ->findAll();
        return $this->render('prestation/list.html.twig', [
            'adherents' => $adherents
        ]);
    }

    /**
     * @Route("/prestation/list/{id}", name="prestation_consomme")
     */
    public function showPrestation(Pac $pac)
    {
        return $this->render('prestation/consomme.html.twig', [
            'pac' => $pac
        ]);
    }

    /**
     * @Route("/prestation/{id}", name="prestation_save")
     */
    public function save(Pac $pac)
    {
        return $this->render('prestation/save.html.twig', [
            'pac' => $pac,
        ]);
    }
}
