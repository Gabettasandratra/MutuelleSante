<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Exercice;
use App\Entity\Parametre;
use App\Form\ExerciceType;
use App\Service\ExerciceService;
use App\Service\ParametreService;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParametreController extends AbstractController
{
    /**
     * @Route("/parametre/mutuelle", name="parametre_mutuelle")
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

    /**
     * @Route("/parametre/exercice", name="parametre_exercice")
     */
    public function exercice(Request $request)
    {        
        $exercices = $this->getDoctrine()->getRepository(Exercice::class)->findAll();
        return $this->render('parametre/exercice.html.twig', [
            'exercices' => $exercices
        ]);
    }

    /**
     * @Route("/parametre/exercice/configurer", name="parametre_exercice_configurer")
     */
    public function addExercice(Request $request, ExerciceService $exerciceService)
    {      
        $exercice = new Exercice();  
        
        $dateDernier = $this->getDoctrine()->getRepository(Exercice::class)->findFinExercice();        
        if ($dateDernier) {
            $exercice->setDateDebut($dateDernier->add(new \DateInterval('P1D') ));
            $exercice->setDateFin( $dateDernier->add(new \DateInterval('P1Y') ));
        }

        $form = $this->createForm(ExerciceType::class, $exercice);         
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateDebut = $exercice->getDateDebut();
            $dateFin = $exercice->getDateFin();            
            // verifier date de debut si déja configurer
            if ($dateDebut > $dateDernier) {
                $interval = (int) date_diff($dateDebut, $dateFin)->format('%a');
                // verifie la longueur de l'exercice
                if ($interval == 364 || $interval == 365) {
                    $exerciceService->createNewExercice($exercice); // Sauvegarde de l'exercice
                    return $this->redirectToRoute('parametre_exercice');
                } else {
                    $form->get('dateFin')->addError(new FormError("Un exercice doit durée en une année, $interval donné"));
                }
            } else {
                $form->get('dateDebut')->addError(new FormError('Ce date appartient à d\'autre exercice'));
            }
        }
        return $this->render('parametre/configureExercice.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}