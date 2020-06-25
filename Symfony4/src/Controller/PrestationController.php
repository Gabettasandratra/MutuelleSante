<?php

namespace App\Controller;

use App\Entity\Pac;
use App\Entity\Adherent;

use App\Entity\Prestation;
use App\Form\PrestationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PrestationController extends AbstractController
{
    /**
     * @Route("/prestation", name="prestation")
     */
    public function index()
    {
        $pacs = $this->getDoctrine()
                          ->getRepository(Pac::class)
                          ->findAll();
        return $this->render('prestation/index.html.twig', [
            'pacs' => $pacs
        ]);
    }

    /**
     * @Route("/prestation/{id}", name="prestation_beneficiare", requirements={"id"="\d+"})
     */
    public function show(Pac $pac)
    {
        return $this->render('prestation/beneficiaire.html.twig', [
            'pac' => $pac,
        ]);
    }

    /**
     * @Route("/prestation/{id}/add", name="prestation_beneficiare_add", requirements={"id"="\d+"})
     */
    public function addPrestation(Pac $pac, Request $request)
    {
        $prestation = new Prestation();
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('prestation/form.html.twig', [
            'form' => $form->createView(),
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

    
}
