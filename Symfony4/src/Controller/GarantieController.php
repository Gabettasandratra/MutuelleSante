<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Garantie;
use App\Form\GarantieType;

class GarantieController extends AbstractController
{
    /**
     * @Route("/garantie", name="garantie")
     */
    public function index()
    {
        $garanties = $this->getDoctrine()
                          ->getRepository(Garantie::class)
                          ->findAll();
        return $this->render('garantie/index.html.twig', [
            'garanties' => $garanties,
        ]);
    }

    /**
     * @Route("/garantie/new", name="garantie_new")
     * @Route("/garantie/{id}/edit", name="garantie_edit")
     */
    public function create(Garantie $garantie = null, Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        if ($garantie == null) {
            $garantie = new Garantie();
        }
        $formGarantie = $this->createForm(GarantieType::class, $garantie);
        $formGarantie->handleRequest($request);
        if ($formGarantie->isSubmitted() && $formGarantie->isValid()) {
            $garantie->setIsActive(true);
            $manager->persist($garantie);
            $manager->flush();
            return $this->redirectToRoute('garantie');
        }

        return $this->render('garantie/form.html.twig', [
            'form' => $formGarantie->createView(),
            'editMode' => $garantie->getId() !== null
        ]);

    }
}
