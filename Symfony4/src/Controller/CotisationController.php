<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

use App\Entity\Adherent;
use App\Entity\HistoriqueCotisation;
use App\Form\HistoriqueCotisationType;

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

    /**
     * @Route("/cotisation/{id}/pay/{year}", name="cotisation_pay")
     */
    public function pay(Adherent $adherent, $year, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $historiqueCotisation = new HistoriqueCotisation();
        $historiqueCotisation->setAdherent($adherent);
        $historiqueCotisation->setAnnee($year);

        $form = $this->createForm(HistoriqueCotisationType::class, $historiqueCotisation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $historiqueCotisation->setCreatedAt(new \DateTime());        
            $manager->persist($historiqueCotisation);
            $manager->persist($adherent);
            $manager->flush();

            return $this->redirectToRoute('cotisation_show', ['id' => $adherent->getId()]);     
        }
        return $this->render('cotisation/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
