<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Service\ComptaService;

use Doctrine\ORM\EntityRepository;
use App\Entity\HistoriqueCotisation;
use Symfony\Component\Form\FormError;
use App\Form\HistoriqueCotisationType;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CotisationController extends AbstractController
{
    /**
     * @Route("/cotisation", name="cotisation")
     */
    public function index(AdherentRepository $repository, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        return $this->render('cotisation/index.html.twig', [
            'adherents' => $repository->findJoinCompteCotisation($exercice)
        ]);
    }

    /**
     * @Route("/cotisation/{id}", name="cotisation_show")
     */
    public function show(Adherent $adherent, CompteCotisationRepository $repository, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $compteCotisation = $repository->findCompteCotisation($adherent, $exercice);
        return $this->render('cotisation/show.html.twig', [
            'adherent' => $adherent,
            'exercice' => $exercice,
            'compteCotisation' => $compteCotisation
        ]);
    }

    /**
     * @Route("/cotisation/{id}/pay", name="cotisation_pay")
     */
    public function pay(Adherent $adherent, Request $request, ComptaService $comptaService)
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
                            $comptaService->payCotisation($historiqueCotisation);

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

    /**
     * @Route("/cotisation/{adherent_id}/edit/{cotisation_id}", name="cotisation_edit")
     * @ParamConverter("adherent", options={"mapping": {"adherent_id":"id"}})
     * @ParamConverter("historiqueCotisation", options={"mapping": {"cotisation_id":"id"}})
     */
    public function editCotisation(Adherent $adherent, HistoriqueCotisation $historiqueCotisation, Request $request, EntityManagerInterface $manager)
    {
        $montantPaye = $historiqueCotisation->getMontant();
        $form = $this->createFormBuilder($historiqueCotisation)
                    ->add('datePaiement', DateType::class, [
                        'label' => 'Date de paiement',
                    ]) 
                    ->add('montant')
                     ->add('tresorerie', EntityType::class, [
                        'label' => 'Mode de paiement',
                        'class' => Compte::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->andWhere('c.isTresor = true')
                                    ->orderBy('c.poste', 'ASC');
                        },
                        'choice_label' => 'libelle',
                    ])
                     ->add('reference')
                     ->add('remarque')
                     ->getForm();       
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* Retirer le montant paye dans le compte cotisation */
            $compteCotisation = $historiqueCotisation->getCompteCotisation();
            $compteCotisation->payer(-$montantPaye);

            /* Verifier le montant si supérieur au reste */
            $montant = $historiqueCotisation->getMontant();
            if ($montant <= $compteCotisation->getReste() ) { 
                /* Payer le montant enregistrer */
                $article = $historiqueCotisation->getArticle();
                $article->setMontant($historiqueCotisation->getMontant());
                $article->setPiece($historiqueCotisation->getReference());
                $article->setCompteDebit($historiqueCotisation->getTresorerie());

                $compteCotisation->payer($montant); 
                        
                $manager->persist($historiqueCotisation);
                $manager->flush();

                return $this->redirectToRoute('cotisation_show', ['id' => $adherent->getId()]);                   
            } else {
                $form->get('montant')->addError(new FormError("Désolé mais le montant saisie ne doit pas depasser ".$compteCotisation->getReste(). " (Reste à payer)"));
            }
        }

        return $this->render('cotisation/editCotisation.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
