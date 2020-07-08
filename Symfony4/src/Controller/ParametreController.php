<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Exercice;
use App\Entity\Parametre;
use App\Form\ExerciceType;
use App\Form\ParametersType;
use App\Service\ExerciceService;
use App\Service\ParametreService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParametreController extends AbstractController
{
    /**
     * @Route("/parametre/mutuelle", name="parametre_mutuelle")
     */
    public function mutuelle(ParametreService $paramService, Request $request)
    {
        $allParameters = $this->getDoctrine()->getRepository(Parametre::class)->getParameters();
        if (!$allParameters) {
            $paramService->initialize();
            $allParameters = $this->getDoctrine()->getRepository(Parametre::class)->getParameters();
        }

        $pCotisation = $allParameters['compte_cotisation'];
        $pLabelCotisation = $allParameters['label_cotisation'];
        $pPrestation = $allParameters['compte_prestation'];
        $pLabelPrestation = $allParameters['label_prestation'];
        $pSoins = $allParameters['soins_prestation'];
        $pPercent = $allParameters['percent_prestation'];
        $pPlafond = $allParameters['plafond_prestation'];

        /* Pour bien afficher le formulaire avec les données */
        $compteCot = $this->getDoctrine()->getRepository(Compte::class)->findOneByPoste($pCotisation->getValue());        
        $comptePre = $this->getDoctrine()->getRepository(Compte::class)->findOneByPoste($pPrestation->getValue());        
        $data['compte_cotisation'] = $compteCot;
        $data['label_cotisation'] = $pLabelCotisation->getValue();
        $data['compte_prestation'] = $comptePre;
        $data['label_prestation'] = $pLabelPrestation->getValue();
        $data['percent_prestation'] = $pPercent->getValue();
        $data['plafond_prestation'] = $pPlafond->getValue();
        $data['soins_prestation'] = json_encode($pSoins->getList());
        /* Le data est uniquement pour afficher le données dans le formulaire */

        $form = $this->createForm(ParametersType::class, $data) ;        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Enregistrer tous les parametres données */
            $pCotisation->setValue($form->get('compte_cotisation')->getData()->getPoste());
            $pLabelCotisation->setValue($form->get('label_cotisation')->getData());

            $pPrestation->setValue($form->get('compte_prestation')->getData()->getPoste());
            $pLabelPrestation->setValue($form->get('label_prestation')->getData());
            $pPercent->setValue($form->get('percent_prestation')->getData());
            $pPlafond->setValue($form->get('plafond_prestation')->getData());
            $pSoins->setList(json_decode($form->get('soins_prestation')->getData(), true));

            $this->getDoctrine()->getManager()->flush(); // flush suffit
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