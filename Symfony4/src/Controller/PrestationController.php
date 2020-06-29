<?php

namespace App\Controller;

use App\Entity\Pac;
use App\Entity\Adherent;

use App\Entity\Exercice;
use App\Entity\Prestation;
use App\Form\PrestationType;
use App\Entity\Remboursement;
use App\Form\RemboursementType;
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
     * @Route("/prestation/beneficiaire/{id}", name="prestation_beneficiaire", requirements={"id"="\d+"})
     */
    public function show(Pac $pac)
    {
        return $this->render('prestation/beneficiaire.html.twig', [
            'pac' => $pac,
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}/add", name="prestation_beneficiaire_add", requirements={"id"="\d+"})
     */
    public function addPrestation(Pac $pac, Request $request)
    {
        $prestation = new Prestation($pac);
        $form = $this->createForm(PrestationType::class, $prestation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($prestation);
            $manager->flush();
            return $this->redirectToRoute('prestation_beneficiaire', ['id' => $pac->getId()]);
        }

        return $this->render('prestation/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prestation/adherent", name="prestation_adherent")
     */
    public function adherent()
    {
        $adherents = $this->getDoctrine()
                          ->getRepository(Adherent::class)
                          ->findAll();
        return $this->render('prestation/adherent.html.twig', [
            'adherents' => $adherents
        ]);
    }
    
    /**
     * @Route("/prestation/adherent/{id}", name="prestation_adherent_show", requirements={"id"="\d+"})
     */
    public function prestationAdherent(Adherent $adherent)
    {
        $prestationNotPayed = $this->getDoctrine()->getRepository(Prestation::class)->findNotPayed($adherent);
        return $this->render('prestation/show.html.twig', [
            'adherent' => $adherent,
            'prestationNotPayed' => $prestationNotPayed,
        ]);
    }

    /**
     * @Route("/prestation/adherent/{id}/rembourser", name="prestation_adherent_rembourser", requirements={"id"="\d+"})
     */
    public function rembourserAdherent(Adherent $adherent, Request $request)
    {
        $exercice = $this->getDoctrine()->getRepository(Exercice::class)->findCurrent();
        $montantNoPayed = $this->getDoctrine()->getRepository(Prestation::class)->getMontantNotPayed($adherent);
        $remboursement = new Remboursement($adherent, $exercice, $montantNoPayed[0][2]);
        $form = $this->createForm(RemboursementType::class, $remboursement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($remboursement); 
            // A chaque prestation non payer, on paye
            $prestationNotPayed = $this->getDoctrine()->getRepository(Prestation::class)->findNotPayed($adherent);
            foreach ($prestationNotPayed as $prestation) {
                $prestation->setIsPaye(true);
                $prestation->setRemboursement($remboursement);
                $manager->persist($prestation); 
            }

            $manager->flush();
            return $this->redirectToRoute('prestation_adherent_show', ['id' => $adherent->getId()]);
        }

        return $this->render('prestation/rembourser.html.twig', [
            'adherent' => $adherent,
            'form' => $form->createView(),
        ]);
    }
}
