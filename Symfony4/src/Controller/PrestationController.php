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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PrestationController extends AbstractController
{
    /**
     * @Route("/prestation", name="prestation")
     */
    public function index()
    {
        $pacs = $this->getDoctrine()->getRepository(Pac::class)->findAll();
        $pourcentagePaye = $this->getDoctrine()->getRepository(Parametre::class)->findOneByNom('percent_prestation');
        return $this->render('prestation/index.html.twig', [
            'pacs' => $pacs,
            'percent' => (float) $pourcentagePaye->getValue()
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}", name="prestation_beneficiaire", requirements={"id"="\d+"})
     */
    public function show(Pac $pac)
    {
        $exercice = $this->getDoctrine()
                         ->getRepository(Exercice::class)
                         ->findCurrent();

        $prestations = $this->getDoctrine()
                          ->getRepository(Prestation::class)
                          ->findPrestation($exercice, $pac);   
        $totalRembourse = 0;
        foreach ($prestations as $prestation) {
            $totalRembourse += $prestation->getRembourse();
        }

        $info = [
            'tRemb' => $totalRembourse,
            'plafond' => $exercice->getCotAncien()*2,
        ];

        return $this->render('prestation/beneficiaire.html.twig', [
            'pac' => $pac,
            'info' => $info,
            'prestations' => $prestations,
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}/decompte", name="prestation_beneficiaire_decompte", requirements={"id"="\d+"})
     */
    public function addDecompte(Pac $pac)
    {
        $generatedNumero = $this->getDoctrine()->getRepository(Prestation::class)->generateNumero($pac);
        $soins = $this->getDoctrine()->getRepository(Parametre::class)->findOneByNom('soins_prestation');
        return $this->render('prestation/decompte.html.twig', [
            'pac' => $pac,
            'numero' => $generatedNumero,
            'soins' => $soins->getList(),
        ]);
    }

    /**
     * @Route("/prestation/beneficiaire/{id}/decompte/save", name="prestation_beneficiaire_save_decompte", requirements={"id"="\d+"})
     * @Method("POST")
     */
    public function saveJsonDecompte(Pac $pac, Request $request, ValidatorInterface $validator)
    {
        $manager = $this->getDoctrine()->getManager();
        $data = json_decode( $request->getContent(), true);
        $prestations = $data['prestations'];
        if (!$prestations) {
            return new JsonResponse([
                'hasError' => false,
                'ErrorMessages' => [ 'Le décompte de prestation est invalide' ]
            ]);
        }
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
        }

        $manager->flush();
        return new JsonResponse([
            'hasError' => false
        ]);
    }

    /**
     * @Route("/prestation/adherent", name="prestation_adherent")
     */
    public function adherent()
    {
        $adherents = $this->getDoctrine()
                          ->getRepository(Adherent::class)
                          ->findAll();
        $pourcentagePaye = $this->getDoctrine()->getRepository(Parametre::class)->findOneByNom('percent_prestation');
        return $this->render('prestation/adherent.html.twig', [
            'adherents' => $adherents,
            'percent' => (float) $pourcentagePaye->getValue()
        ]);
    }
    
    /**
     * @Route("/prestation/adherent/{id}", name="prestation_adherent_show", requirements={"id"="\d+"})
     */
    public function prestationAdherent(Adherent $adherent)
    {
        $exercice = $this->getDoctrine()->getRepository(Exercice::class)->findCurrent();
        $plafondPrestation = $this->getDoctrine()->getRepository(Parametre::class)->findOneByNom('plafond_prestation');

        $remboursements = $this->getDoctrine()->getRepository(Remboursement::class)->findRemboursement($exercice, $adherent);
        $totalRembourse = 0;
        foreach ($remboursements as $remboursement) {
            $totalRembourse += $remboursement->getMontant();
        }
        $compteCotisation = $adherent->getCompteCotisation($exercice);
        
        $plafond = $compteCotisation->getPaye() * ((float) $plafondPrestation->getValue());
        $info = [
            'tRemb' => $totalRembourse,
            'percue' => $compteCotisation->getPaye(),
            'due' => $compteCotisation->getDue(),
            'plafond' => $plafond,
            'reste' => $plafond - $totalRembourse,
        ];

        $prestationNotPayed = $this->getDoctrine()->getRepository(Prestation::class)->findNotPayed($adherent);

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
    public function rembourserAdherent(Adherent $adherent, Request $request, ComptaService $comptaService)
    {
        $exercice = $this->getDoctrine()->getRepository(Exercice::class)->findCurrent();
        $montantNoPayed = $this->getDoctrine()->getRepository(Prestation::class)->getMontantNotPayed($adherent);
        $remboursement = new Remboursement($adherent, $exercice, $montantNoPayed[0][2]);
        $form = $this->createForm(RemboursementType::class, $remboursement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
        }

        return $this->render('prestation/rembourser.html.twig', [
            'adherent' => $adherent,
            'form' => $form->createView(),
        ]);
    }
}
