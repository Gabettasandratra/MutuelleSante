<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Adherent;

class CotisationController extends AbstractController
{
    /**
     * @Route("/cotisation", name="cotisation")
     */
    public function index()
    {
        $adherents = $this->getDoctrine()
                          ->getRepository(Adherent::class)
                          ->findAll();
        return $this->render('cotisation/index.html.twig', [
            'adherents' => $adherents
        ]);
    }

    /**
     * @Route("/cotisation/{id}", name="cotisation_show")
     */
    public function show(Adherent $adherent)
    {
        return $this->render('cotisation/show.html.twig', [
            'adherent' => $adherent
        ]);
    }
}
