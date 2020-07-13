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
     * @Route("/comptabilite/tresorerie", name="tresorerie")
     */
    public function index(CompteRepository $repositoryCompte)
    {
        return $this->render('tresorerie/index.html.twig', [
            'comptes' => $repositoryCompte->findTresorerie()
        ]);
    }

    /**
     * @Route("/comptabilite/tresorerie/ajout", name="tresorerie_add")
     * @Route("/comptabilite/tresorerie/{id}/edit", name="tresorerie_edit", requirements={"id"="\d+"})
     */
    public function add(Compte $compteTresorerie = null, Request $request, EntityManagerInterface $manager)
    {
        if ($compteTresorerie === null) {
            $compteTresorerie = new Compte();
            $compteTresorerie->setIsTresor(true);
            $compteTresorerie->setClasse('5-COMPTE FINANCIERS');
            $compteTresorerie->setCategorie('COMPTES DE BILAN');
            $compteTresorerie->setType(true); // Actif
        }
        $form = $this->createFormBuilder($compteTresorerie)
                     ->add('poste')
                     ->add('titre')
                     ->add('acceptIn')
                     ->add('acceptOut')
                     ->add('codeJournal')
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
            'editMode' => $compteTresorerie->getId() !== null
        ]);
    }
}
