<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Parametre;
use App\Service\ParametreService;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParametreController extends AbstractController
{
    /**
     * @Route("/parametre/comptabilite", name="parametre_compta")
     */
    public function compta(ParametreService $paramService, Request $request)
    {
        $test = $this->getDoctrine()->getRepository(Parametre::class)->findByNom('compte_cotisation'); // Test si initalize
        if (!$test) {
            $paramService->initialize();
        }

        $pCotisation = $this->getDoctrine()->getRepository(Parametre::class)->findOneByNom('compte_cotisation');
        $pPrestation = $this->getDoctrine()->getRepository(Parametre::class)->findOneByNom('compte_prestation');

        $form = $this->createFormBuilder()
                     ->add('compteCotisation', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.classe = \'7-COMPTES DE PRODUITS\'')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                            return $c->getPoste().' | '.$c->getTitre();
                        },
                     ])
                     ->add('comptePrestation', EntityType::class, [
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.classe = \'6-COMPTES DE CHARGES\'')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => function ($c) {
                            return $c->getPoste().' | '.$c->getTitre();
                        },
                     ])
                     ->getForm();          
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pCotisation->setValue($form->get('compteCotisation')->getData()->getPoste());
            $pPrestation->setValue($form->get('comptePrestation')->getData()->getPoste());

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($pCotisation);
            $manager->persist($pPrestation);
            $manager->flush();
        }

        return $this->render('parametre/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
