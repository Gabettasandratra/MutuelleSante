<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Form\CompteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TresorerieController extends AbstractController
{
    /**
     * @Route("/tresorerie", name="tresorerie")
     */
    public function index()
    {
        $compteTresoreries = $this->getDoctrine()
                                 ->getRepository(Compte::class)
                                 ->findByIsTresor(true);

        return $this->render('tresorerie/index.html.twig', [
            'comptes' => $compteTresoreries,
        ]);
    }

    /**
     * @Route("/tresorerie/ajout", name="tresorerie_add")
     */
    public function add(Request $request)
    {
        $compteTresorerie = new Compte();
        $compteTresorerie->setIsTresor(true);
        $compteTresorerie->setClasse('COMPTE FINANCIERS');
        $compteTresorerie->setCategorie('COMPTES DE BILAN');
        $compteTresorerie->setType(true); // Actif 

        $form = $this->createFormBuilder($compteTresorerie)
                     ->add('poste')
                     ->add('titre')
                     ->add('libelle')
                     ->add('note')
                     ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($compteTresorerie);
            $manager->flush();
            return $this->redirectToRoute('tresorerie');
        }

        return $this->render('tresorerie/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
