<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Article;
use App\Form\CompteType;
use App\Service\ComptaService;
use Doctrine\ORM\EntityRepository;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    /**
     * @Route("/comptabilite/tresorerie/cheque/{id}", name="tresorerie_cheque")
     */
    public function manageCheque(Compte $compteCheque, ArticleRepository $repository, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        return $this->render('tresorerie/cheque.html.twig', [
            'compte' => $compteCheque,
            'cheques' => $repository->findCheques($exercice, $compteCheque),
        ]);
    }

    /**
     * @Route("/comptabilite/tresorerie/cheque/{id}/verser", name="tresorerie_cheque_verser")
     */
    public function versementCheque(Article $articleCheque, Request $request, ComptaService $comptaService)
    {
        $form = $this->createFormBuilder()
                    ->add('date', DateType::class, [
                        'data' => new \DateTime(),
                        'constraints' => [new LessThanOrEqual("today")]
                    ])
                    ->add('banque', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.isTresor = true')
                                    ->andWhere('c.poste LIKE :poste')
                                    ->setParameter('poste', '512%')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => 'titre'
                    ])
                    ->add('piece', TextType::class)
                    ->getForm();       
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date')->getData();
            $banque = $form->get('banque')->getData();
            $piece = $form->get('piece')->getData();
            if ($comptaService->verserCheque($articleCheque, $banque, $date, $piece)) // Si success
                return $this->redirectToRoute('tresorerie_cheque', ['id'=>$articleCheque->getCompteDebit()->getId()]);
        }

        return $this->render('tresorerie/versementCheque.html.twig', [
            'cheque' => $articleCheque,
            'form' => $form->createView()
        ]);
    }
}
