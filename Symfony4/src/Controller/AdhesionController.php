<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Adherent;
use App\Form\AdherentType;

class AdhesionController extends AbstractController
{
    /**
     * @Route("/adhesion", name="adhesion")
     */
    public function index()
    {
        return $this->render('adhesion/index.html.twig');
    }

    /**
     * @Route("/adhesion/inscrire", name="adhesion_new")
     * @Route("/adhesion/{id}/edit", name="adhesion_edit")
     */
    public function form(Adherent $adherent = null, Request $request)
    { 
        $manager = $this->getDoctrine()->getManager();
        if ($adherent === null) {
            $adherent = new Adherent();
        }
        $formAdherent = $this->createForm(AdherentType::class, $adherent);
        $formAdherent->handleRequest($request);
        if ($formAdherent->isSubmitted() && $formAdherent->isValid()) {
            $adherent->setCreatedAt(new \DateTime());
            $manager->persist($adherent);
            $manager->flush();

            return $this->redirectToRoute('adhesion_show', ['id' => $adherent->getId()]);
        }
        return $this->render('adhesion/form.html.twig', [
            'form' => $formAdherent->createView(),
            'editMode' => $adherent->getId() != null
        ]);
    }

    /**
     * @Route("/adhesion/{id}", name="adhesion_show")
     */
    public function show(Adherent $adherent)
    {
        return $this->render('adhesion/show.html.twig', [
            'adherent' => $adherent
        ]);
    }
}
