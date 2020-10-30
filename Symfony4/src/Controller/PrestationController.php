<?php

namespace App\Controller;

use App\Entity\Pac;
use App\Entity\Detail;

use App\Entity\Adherent;
use App\Entity\Exercice;
use App\Entity\Parametre;
use App\Entity\Prestation;
use App\Form\PrestationType;
use App\Service\ExcelReader;
use App\Entity\Remboursement;
use App\Service\ComptaService;
use App\Form\RemboursementType;
use App\Repository\PacRepository;
use App\Repository\CompteRepository;
use App\Repository\DetailRepository;
use Symfony\Component\Form\FormError;
use App\Repository\AdherentRepository;
use App\Repository\ParametreRepository;
use App\Repository\AnalytiqueRepository;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RemboursementRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        

        /* Verifie si le béneficiaire possede le droit */
        $adherent = $pac->getAdherent();
        $compteCotisation = $repositoryCompteCotisation->findCompteCotisation($adherent, $exercice);
        

        $totalRembourse = 0;
        foreach ($prestations as $prestation) {
            $totalRembourse += $prestation->getRembourse();
        }

        $info = [
            'possedeDroit' => $compteCotisation->getPourcentagePaye() >= $percentPrestation,
            'tRemb' => $totalRembourse,
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
    public function addDecompte(Pac $pac, AnalytiqueRepository $repoServiceSante, PrestationRepository $repositoryPrestation, CompteCotisationRepository $repoCompte, ParametreRepository $repositoryParametre,RemboursementRepository $repoRemb, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $generatedNumero = $repositoryPrestation->generateNumero($pac->getAdherent(), $exercice);
        $remb = $repositoryParametre->findOneByNom('percent_rembourse_prestation')->getValue();
        $remb_plafond = $repositoryParametre->findOneByNom('percent_rembourse_prestation_plafond')->getValue();

        // Calcul si le remboursement est normale ou plafoné
        $coefPlafond = (float)$repositoryParametre->findOneByNom('plafond_prestation')->getValue();
        $cotPaye = $repoCompte->findCompteCotisation($pac->getAdherent(), $exercice)->getPaye();
        $plafond = $cotPaye * $coefPlafond;
        $montRemb = $repoRemb->findTotalRemb($exercice, $pac->getAdherent()); // Montant remboursé
        
        $percent = ($plafond > $montRemb)?$remb:$remb_plafond;
        return $this->render('prestation/decompte.html.twig', [
            'pac' => $pac,
            'numero' => $generatedNumero,
            'remb' => $percent,
            'soins' => $repoServiceSante->findServiceSante()
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}/decompte/save", name="prestation_beneficiaire_save_decompte", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function saveJsonDecompte(Pac $pac, Request $request, ValidatorInterface $validator, ComptaService $comptaService, EntityManagerInterface $manager, SessionInterface $session)
    {
        $exercice = $session->get('exercice');

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
            $prestation->setPrestataire($prestationJs['prestataire']);
            $prestation->setFacture($prestationJs['facture']);
            $prestation->setUser($this->getUser()); // Utilisateur qui l'a saisie
            $prestation->setDecompte($data['numero']);

            $errors = $validator->validate($prestation);
            if (0 === count($errors) && $exercice->check($prestation->getDate())) {
                // Ajout de chaque detail
                foreach ($prestationJs['details'] as $donnee) {
                    $detail = new Detail($donnee['montant'], $donnee['detail']);
                    $prestation->addDetail($detail);
                }
                $manager->persist($prestation);
            } else {    
                $retour['hasError'] = true;
                $retour['ErrorMessages'][] = "Les données de la prestation #". ($key+1) ." est invalide"; 
                foreach ($errors as $error) {
                    $retour['ErrorMessages'][] = $error->getMessage();
                }

                if (!$exercice->check($prestation->getDate())) {
                    $retour['ErrorMessages'][] = "La date ". $prestation->getDate()->format('d/m/Y')." n'appartient pas à l'exercice " . $exercice->getAnnee();
                }
                return new JsonResponse($retour);
            }
        }
        $manager->flush();

        return new JsonResponse([
            'hasError' => false
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/decompte/import", name="prestation_beneficiaire_import_decompte")
     */
    public function importDecompte(Request $request, ExcelReader $excelReader)
    {
        $form = $this->createFormBuilder()
                    ->add('file', FileType::class, [
                        'mapped' => false,
                        'required' => true,
                    ])
                    ->add('save', SubmitType::class, ['label' => 'Importer xlsx'])
                    ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // read data from Excel file
            $xlsxFile = $form->get('file')->getData();
            if ($xlsxFile) {
                $output = $excelReader->saveDecompte($xlsxFile);  
                if ($output['hasError'] === false) {
                    return $this->redirectToRoute('prestation_beneficiaire', ['id' => $pac->getId()]); 
                } else {
                    foreach ($output['ErrorMessages'] as $message) {
                        $form->get('file')->addError(new FormError($message));
                    }
                }             
            }
            
        }
        
        return $this->render('adhesion/addPacXlsx.html.twig', [
            'form' => $form->createView(),
            'adherent' => $adherent,
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
            $wait = $repositoryAdherent->findPrestationAttente($exercice, $adherent);
            $retour[] = [
                'id' => $adherent->getId(),
                'numero' => $adherent->getNumero(),
                'nom' => $adherent->getNom(),
                'attenteDecision' => $wait['nonDecide'],
                'attentePaiement' => $wait['nonPaye'],
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
        // Serialization du prestation
        $prestationNotPayed = $repositoryPrestation->findNotPayed($adherent);
        $donnesSerialize = [];
        foreach ($prestationNotPayed as $prestation) {
            $donnesSerialize[] = [
                'id'=>$prestation->getId(), 
                'date'=>$prestation->getDate()->format('d/m/Y'), 
                'pac'=>$prestation->getPac()->getMatricule(),
                'designation'=>$prestation->getDesignation(),  
                'frais'=>$prestation->getFrais(), 
                'rembourse'=>$prestation->getRembourse(), 
                'status'=>$prestation->getStatus(), 
                'prestataire'=>$prestation->getPrestataire(),
                'facture'=>$prestation->getFacture(), 
                'decompte'=>$exercice->getAnnee().'/'.$prestation->getDecompte(), 
                'details'=>$prestation->getJsonDetails()
            ];
        }
        $json = json_encode($donnesSerialize);          
        // Le valeur de pourcentage
        $remb = $repositoryParametre->findOneByNom('percent_rembourse_prestation')->getValue(); // PAr defaut
        $remb_plafond = $repositoryParametre->findOneByNom('percent_rembourse_prestation_plafond')->getValue(); // Si on a sauter le plafond
        $percent = ['normal' => $remb, 'plafond' => $remb_plafond];

        return $this->render('prestation/show.html.twig', [
            'adherent' => $adherent,
            'info' => $info,
            'remboursements' => $remboursements,
            'percent' => $percent,
            'prestationNotPayed' => $prestationNotPayed,
            'json' => $json 
        ]);
    }

    /**
     * @Route("/prestation/decider", name="prestation_decider_json")
     */
    public function ajaxUpdatePrestation(PrestationRepository $repoPre, Request $request, DetailRepository $repoDetail, EntityManagerInterface $manager, ComptaService $comptaService)
    {
        $data = json_decode( $request->getContent(), true);
        $prestation = $repoPre->find($data['id']);
        $prestation->setFrais($data['frais']);
        $prestation->setRembourse($data['rembourse']);
        if ($data['rembourse'] > 0) 
            $prestation->setStatus(1);
        else
            $prestation->setStatus(-1);
        
        foreach ($data['details'] as $d) {
            $detail = $repoDetail->find($d['id']);
            if ($detail->getEtat() !== $d['etat'])
                $detail->setEtat($d['etat']);
        }
        $manager->flush();

        // Suvegarde des dettes
        $comptaService->updateDetteRemb();
        dump('Affter update');
        return new JsonResponse([
            'hasError' => false,
            'message' => "Mis à jour avec success",
            'data' => $data,
        ]);
    }

    /**
     * @Route("/prestation/adherent/remboursement/{id}", name="prestation_adherent_remboursement", requirements={"id"="\d+"})
     */
    public function detailRemboursement(Remboursement $remboursement, PrestationRepository $repo)
    {
        return $this->render('prestation/detailRemboursement.html.twig', [
            'adherent' => $remboursement->getAdherent(),
            'remboursement' => $remboursement,
            'donnees' => $repo->findDetailRemb($remboursement)
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
            'edit' => false,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prestation/adherent/remboursement/{id}/edit", name="prestation_adherent_remboursement_edit", requirements={"id"="\d+"})
     */
    public function editRemboursement(Remboursement $remboursement,Request $request,ComptaService $comptaService,CompteRepository $repositoryCompte)
    {
        $form = $this->createForm(RemboursementType::class, $remboursement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $montant = $form->get('montant')->getData();
            $tresorerie = $form->get('tresorerie')->getData();
            $solde = $repositoryCompte->findSolde($tresorerie);
            if ($montant <= $solde) {
                $remboursement = $comptaService->payRemboursement($remboursement); // Modification
                return $this->redirectToRoute('prestation_adherent_show', ['id' => $remboursement->getAdherent()->getId()]);
            } else {
                $form->get('tresorerie')->addError(new FormError("Le solde negatif est interdit pour les trésoreries. Solde actuelle $solde"));
            }
        }

        return $this->render('prestation/rembourser.html.twig', [
            'adherent' => $remboursement->getAdherent(),
            'edit' => true,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/prestation/rapport", name="prestation_rapport")
     */
    public function rapport(RemboursementRepository $repository, ParametreRepository $repositoryParametre,SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $coefficient_remb = $repositoryParametre->findOneByNom('plafond_prestation')->getValue();
        return $this->render('prestation/rapport.html.twig', [
            'exercice' => $exercice,
            'coefficient' => $coefficient_remb,
            'rembs' => $repository->findRapportRemboursement($exercice)
        ]);
    }
}
