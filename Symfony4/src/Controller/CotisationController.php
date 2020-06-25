<?php

namespace App\Controller;

use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Entity\HistoriqueCotisation;
use Symfony\Component\Form\FormError;

use App\Form\HistoriqueCotisationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CotisationController extends AbstractController
{
    /**
     * @Route("/cotisation", name="cotisation")
     */
    public function index()
    {
        $adherents = $this->getDoctrine()
                          ->getRepository(Adherent::class)
                          ->findAll();
        return $this->render('cotisation/index.html.twig', [
            'adherents' => $adherents
        ]);
    }

    /**
     * @Route("/cotisation/{id}", name="cotisation_show")
     * @Entity("adherent", expr="repository.findOneById(id)")
     */
    public function show(Adherent $adherent)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

        return $this->render('cotisation/show.html.twig', [
            'adherent' => $adherent,
            'exercice' => $exercice
        ]);
    }

    /**
     * @Route("/cotisation/{id}/pay", name="cotisation_pay")
     * @Entity("adherent", expr="repository.findOneById(id)")
     */
    public function pay(Adherent $adherent, Request $request)
    {
        $historiqueCotisation = new HistoriqueCotisation();    
        $form = $this->createForm(HistoriqueCotisationType::class, $historiqueCotisation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get the cotisation account associated
            $exercice = $form->get('exercice')->getData(); // from the choice of the user
            $compteCotisation = $adherent->getCompteCotisation($exercice); 
            if ($compteCotisation !== null) { // si le compte existe bien
                $verifyPrevCompte = $adherent->verifyPrevCompteCotisation($exercice);  // verifier s'il y des non payé
                if ($verifyPrevCompte === null) {
                    if (!$compteCotisation->getIsPaye()) { // si le compte de l'anneé est deja payé
                        $montant = (float) $form->get('montant')->getData();
                        if ($montant <= $compteCotisation->getReste() ) { // si on essaie de payé en avance
                            /* Tout est en regle alors on peut enregistrer */
                            $compteCotisation->payer($montant);
                            $historiqueCotisation->setCompteCotisation($compteCotisation);

                            $manager = $this->getDoctrine()->getManager();
                            $manager->persist($compteCotisation);
                            $manager->persist($historiqueCotisation);
                            $manager->flush();

                            return $this->redirectToRoute('cotisation_show', ['id' => $adherent->getId()]);                   
                        } else {
                            $form->get('montant')->addError(new FormError("Désolé mais le montant saisie ne doit pas depasser ".$compteCotisation->getReste(). " (Reste à payer)"));
                        }
                    } else {
                        $form->get('exercice')->addError(new FormError("Désolé mais la cotisation de l'année ". $exercice->getAnnee()." est déja payé pour cette congrégation"));
                    }
                } else {
                    $form->get('exercice')->addError(new FormError("Désolé mais la cotisation de l'année ". $verifyPrevCompte->getExercice()->getAnnee()." n'est pas encore payé pour cette congrégation"));
                }
            } else {
                $form->get('exercice')->addError(new FormError("Désolé mais l'année" . $exercice->getAnnee(). " est incohérent pour cette congrégation"));
            }
            
        }
        return $this->render('cotisation/form.html.twig', [
            'form' => $form->createView(),
            'adherent' => $adherent,
        ]);
    }
}
