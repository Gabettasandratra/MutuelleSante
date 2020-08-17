<?php

namespace App\Controller;

use App\Entity\Pac;
use App\Entity\Adherent;

use App\Entity\Exercice;
use App\Entity\Parametre;
use App\Entity\Prestation;
use App\Form\PrestationType;
use App\Entity\Remboursement;
use App\Service\ComptaService;
use App\Form\RemboursementType;
use App\Repository\PacRepository;
use App\Repository\CompteRepository;
use Symfony\Component\Form\FormError;
use App\Repository\AdherentRepository;
use App\Repository\ParametreRepository;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RemboursementRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PrestationController extends AbstractController
{
    /**
     * @Route("/prestation/beneficiaire", name="prestation")
     */
    public function index(PacRepository $repositoryPac)
    {
        return $this->render('prestation/index.html.twig', [
            'pacs' => $repositoryPac->findBy(['isSortie' => false]),
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}", name="prestation_beneficiaire", requirements={"id"="\d+"})
     */
    public function show(Pac $pac, PrestationRepository $repositoryPrestation, ParametreRepository $repositoryParametre, CompteCotisationRepository $repositoryCompteCotisation, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $prestations = $repositoryPrestation->findPrestation($exercice, $pac);  

        $percentPrestation = (float) $repositoryParametre->findOneByNom('percent_prestation')->getValue();
        $plafondPrestation = (float) $repositoryParametre->findOneByNom('plafond_prestation')->getValue();

        /* Verifie si le béneficiaire possede le droit */
        $adherent = $pac->getAdherent();
        $compteCotisation = $repositoryCompteCotisation->findCompteCotisation($adherent, $exercice);
        if ($compteCotisation->getPourcentagePaye() >= $percentPrestation) {
            
        }

        $totalRembourse = 0;
        foreach ($prestations as $prestation) {
            $totalRembourse += $prestation->getRembourse();
        }

        $info = [
            'possedeDroit' => $compteCotisation->getPourcentagePaye() >= $percentPrestation,
            'tRemb' => $totalRembourse,
            'plafond' => $exercice->getCotAncien() * $plafondPrestation
        ];

        return $this->render('prestation/beneficiaire.html.twig', [
            'pac' => $pac,
            'info' => $info,
            'exercice' => $exercice,
            'prestations' => $prestations,
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}/decompte", name="prestation_beneficiaire_decompte", requirements={"id"="\d+"})
     */
    public function addDecompte(Pac $pac, PrestationRepository $repositoryPrestation, ParametreRepository $repositoryParametre)
    {
        $generatedNumero = $repositoryPrestation->generateNumero($pac);
        $soins = $repositoryParametre->findOneByNom('soins_prestation');
        $remb = $repositoryParametre->findOneByNom('percent_rembourse_prestation');

        return $this->render('prestation/decompte.html.twig', [
            'pac' => $pac,
            'numero' => $generatedNumero,
            'remb' => $remb->getValue(),
            'soins' => $soins->getList(),
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}/decompte/save", name="prestation_beneficiaire_save_decompte", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function saveJsonDecompte(Pac $pac, Request $request, ValidatorInterface $validator, ComptaService $comptaService, EntityManagerInterface $manager)
    {
        $data = json_decode( $request->getContent(), true);
        $prestations = $data['prestations'];
        if (!$prestations) {
            return new JsonResponse([
                'hasError' => false,
                'ErrorMessages' => [ 'Le décompte de prestation est invalide' ]
            ]);
        }
        $tot_remb = 0; // Total des rembs
        
        foreach ($prestations as $key => $prestationJs) {
            $prestation = new Prestation($pac);
            $prestation->setDate(\DateTime::createFromFormat('d/m/Y', $prestationJs['date']));
            $prestation->setDesignation($prestationJs['designation']);
            $prestation->setFrais($prestationJs['frais']);
            $prestation->setRembourse($prestationJs['rembourse']);
            $prestation->setPrestataire($prestationJs['prestataire']);
            $prestation->setFacture($prestationJs['facture']);
            $prestation->setDecompte($data['numero']);
            $errors = $validator->validate($prestation);
            if (0 === count($errors)) {
                $manager->persist($prestation);
            } else {    
                $retour['hasError'] = true;
                $retour['ErrorMessages'][] = "Les données de la prestation #". ($key+1) ." est invalide"; 
                foreach ($errors as $error) {
                    $retour['ErrorMessages'][] = $error->getMessage();
                }
                return new JsonResponse($retour);
            }

            // If prestation is valid
            $tot_remb += $prestation->getRembourse();
        }
        $manager->flush();

        // Sauvegarde en tant que dette
        $comptaService->saveRemboursement($tot_remb, $pac, $data['numero']);

        return new JsonResponse([
            'hasError' => false
        ]);
    }

    /**
     * @Route("/prestation/adherent", name="prestation_adherent")
     */
    public function adherent(AdherentRepository $repositoryAdherent, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $adherents = $repositoryAdherent->findAll();
        $retour = [];
        foreach ($adherents as $adherent) {
            $retour[] = [
                'id' => $adherent->getId(),
                'numero' => $adherent->getNumero(),
                'nom' => $adherent->getNom(),
                'attente' => $repositoryAdherent->findSommePrestationAttente($exercice, $adherent),
            ];
        }               
        return $this->render('prestation/adherent.html.twig', [
            'adherents' => $retour
        ]);
    }
    
    /**
     * @Route("/prestation/adherent/{id}", name="prestation_adherent_show", requirements={"id"="\d+"})
     */
    public function prestationAdherent(Adherent $adherent, CompteCotisationRepository $repository, ParametreRepository $repositoryParametre, RemboursementRepository $repositoryRemboursement, PrestationRepository $repositoryPrestation, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $plafondPrestation = $repositoryParametre->findOneByNom('plafond_prestation');

        $remboursements = $repositoryRemboursement->findRemboursement($exercice, $adherent);
        $totalRembourse = 0;
        foreach ($remboursements as $remboursement) {
            $totalRembourse += $remboursement->getMontant();
        }

        $compteCotisation = $repository->findCompteCotisation($adherent, $exercice);
        
        /* A DEMANDER  */
        $plafond = $compteCotisation->getPaye() * ((float) $plafondPrestation->getValue());
        $info = [
            'tRemb' => $totalRembourse,
            'percue' => $compteCotisation->getPaye(),
            'due' => $compteCotisation->getDue(),
            'plafond' => $plafond,
            'reste' => $plafond - $totalRembourse,
        ];

        $prestationNotPayed = $repositoryPrestation->findNotPayed($adherent);

        return $this->render('prestation/show.html.twig', [
            'adherent' => $adherent,
            'info' => $info,
            'remboursements' => $remboursements,
            'prestationNotPayed' => $prestationNotPayed,
        ]);
    }

    /**
     * @Route("/prestation/adherent/{adh_id}/remboursement/{remb_id}", name="prestation_adherent_remboursement", requirements={"id"="\d+"})
     * @ParamConverter("adherent", options={"mapping": {"adh_id":"id"}})
     * @ParamConverter("remboursement", options={"mapping": {"remb_id":"id"}})
     */
    public function detailRemboursement(Adherent $adherent, Remboursement $remboursement)
    {
        return $this->render('prestation/detailRemboursement.html.twig', [
            'adherent' => $adherent,
            'remboursement' => $remboursement,
        ]);
    }

    /**
     * @Route("/prestation/adherent/{id}/rembourser", name="prestation_adherent_rembourser", requirements={"id"="\d+"})
     */
    public function rembourserAdherent(Adherent $adherent, Request $request, ComptaService $comptaService, CompteRepository $repositoryCompte, PrestationRepository $repositoryPrestation)
    {
        $exercice = $this->getDoctrine()->getRepository(Exercice::class)->findCurrent();
        $montantNoPayed = $repositoryPrestation->getMontantNotPayed($adherent);
        $remboursement = new Remboursement($adherent, $exercice, $montantNoPayed[0][2]);
        $form = $this->createForm(RemboursementType::class, $remboursement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $montant = $form->get('montant')->getData();
            $tresorerie = $form->get('tresorerie')->getData();
            $solde = $repositoryCompte->findSolde($tresorerie);

            if ($montant <= $solde) {
                
                $remboursement = $comptaService->payRemboursement($remboursement);
                $prestationNotPayed = $this->getDoctrine()->getRepository(Prestation::class)->findNotPayed($adherent);
                $manager = $this->getDoctrine()->getManager();
                foreach ($prestationNotPayed as $prestation) {
                    $prestation->setIsPaye(true);
                    $prestation->setRemboursement($remboursement);
                    $manager->persist($prestation); 
                }
                $manager->flush();
                return $this->redirectToRoute('prestation_adherent_show', ['id' => $adherent->getId()]);

            } else {
                $form->get('tresorerie')->addError(new FormError("Le solde negatif est interdit pour les trésoreries. Solde actuelle $solde"));
            }
        }

        return $this->render('prestation/rembourser.html.twig', [
            'adherent' => $adherent,
            'form' => $form->createView(),
        ]);
    }
}
