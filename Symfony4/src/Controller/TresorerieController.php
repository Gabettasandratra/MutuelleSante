<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Form\CompteType;
use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TresorerieController extends AbstractController
{
    /**
     * @Route("/tresorerie", name="tresorerie")
     */
    public function index(CompteRepository $repositoryCompte)
    {
        return $this->render('tresorerie/index.html.twig', [
            'comptes' => $repositoryCompte->findTresorerie()
        ]);
    }

    /**
     * @Route("/tresorerie/ajout", name="tresorerie_add")
     */
    public function add(Request $request, EntityManagerInterface $manager)
    {
        $compteTresorerie = new Compte();
        $compteTresorerie->setIsTresor(true);
        $compteTresorerie->setClasse('5-COMPTE FINANCIERS');
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
            $manager->persist($compteTresorerie);
            $manager->flush();
            return $this->redirectToRoute('tresorerie');
        }

        return $this->render('tresorerie/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
