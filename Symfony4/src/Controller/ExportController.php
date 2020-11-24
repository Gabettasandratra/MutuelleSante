<?php

namespace App\Controller;

use App\Pdf\Pdf;
use App\Entity\Adherent;
use App\Pdf\PDFMutuelle;
use App\Service\ExportExcel;
use App\Entity\Remboursement;
use App\Service\ConfigEtatFi;
use App\Repository\PacRepository;
use App\Repository\CompteRepository;
use App\Repository\ArticleRepository;
use App\Repository\JournalRepository;
use App\Repository\AdherentRepository;
use App\Repository\ParametreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportController extends AbstractController
{
    /**
     * @Route("/export/rapport/cotisation", name="export_rapport_cotisations")
     */
    public function rapportCotisation(ExportExcel $exportService)
    {
        $filename = $exportService->getInfosCotisation();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/export/rapport/adhesion", name="export_rapport_adhesions")
     */
    public function rapportAdhesion(ExportExcel $exportService)
    {
        $filename = $exportService->getListeAdherent();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/export/rapport/prestation", name="export_rapport_prestations")
     */
    public function rapportPrestation(ExportExcel $exportService)
    {
        $filename = $exportService->getRemboursement();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/export/rapport/journal", name="export_rapport_journal")
     */
    public function rapportJournal(ExportExcel $exportService)
    {
        $filename = $exportService->exportJournaux();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/export/rapport/grandlivre", name="export_rapport_grandlivre")
     */
    public function rapportGrandlivre(ExportExcel $exportService)
    {
        $filename = $exportService->exportGrandlivre();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/export/rapport/balance", name="export_rapport_balance")
     */
    public function rapportBalance(ExportExcel $exportService)
    {
        $filename = $exportService->exportBalance();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    /**
     * @Route("/export/detail/{id}", name="export_detail_remboursement")
     */
    public function detail(Remboursement $remboursement, ExportExcel $exportService)
    {
        $filename = $exportService->getDetailRemboursement($remboursement);
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }
    

    /**
     * @Route("/export/beneficiaire/{id}", name="export_beneficiaire")
     */
    public function listeBeneficiaire(Adherent $adherent, ExportExcel $exportService)
    {
        $filename = $exportService->getListeBeneficiaire($adherent);
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }
    
    /**
     * @Route("/export/{filename}", name="export")
     */
    public function index($filename, ExportExcel $exportService)
    {
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /* LES PDFS DE LA MUTUELLE SANTE */
    /**
     * @Route("/pdf/adhesion/congregations", name="pdf_congregations")
     */
    public function pdfCongregation(SessionInterface $session, AdherentRepository $repo, Request $request)
    {
        $exercice = $session->get('exercice');
        $fin = $request->query->get('fin');
        $type = $request->query->get('type');

        // Recupere la date de debut et fin
        $periode['debut'] = \DateTime::createFromFormat('dmY', '01011900');
        $periode['fin'] = \DateTime::createFromFormat('d/m/Y', $fin);
        $congs = $repo->findDateInscription($periode['debut'], $periode['fin']);

        $pdf = new PDFMutuelle($periode, "Liste des congrégations");
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'A4');
        
        $pdf->adhesionTable($congs);

        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /**
     * @Route("/pdf/adhesion/beneficiaires/{id}", name="pdf_beneficiaires")
     */
    public function pdfBeneficiaires(Adherent $adherent, SessionInterface $session, AdherentRepository $repoAdh, PacRepository $repoPac, Request $request)
    {
        $exercice = $session->get('exercice');
        
        
        $pdf = new PDFMutuelle('liste benef', "Liste de bénéficiaire");
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'A4');
        
        $pdf->beneficiaireTable($adherent->getPacs());
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /* LES PDFS DE LA COMPTABILITE */

    /**
     * @Route("/pdf/balance/{debut}/{fin}", name="pdf_balance")
     */
    public function pdfBalance($debut, $fin,ParametreRepository $parametreRepo, ArticleRepository $repo)
    {
        $dateDebut = \DateTime::createFromFormat('dmY', $debut);
        $dateFin = \DateTime::createFromFormat('dmY', $fin);

        $periode['debut'] = $dateDebut;
        $periode['fin'] = $dateFin;

        $donnees = $repo->findBalance($dateDebut, $dateFin);
        $pdf = new Pdf($periode,$parametreRepo->findDonneesMutuelle(), 'Balance des comptes');
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'A4');

        $pdf->balanceTable($donnees);
        
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }    
    /**
     * @Route("/pdf/journal/{code}/{debut}/{fin}", name="pdf_journal")
     */
    public function pdfJournal($code, $debut, $fin, ParametreRepository $parametreRepo,ArticleRepository $repo,JournalRepository $repoJ, CompteRepository $repoCompte)
    {
        $dateDebut = \DateTime::createFromFormat('dmY', $debut);
        $dateFin = \DateTime::createFromFormat('dmY', $fin);

        $periode['debut'] = $dateDebut;
        $periode['fin'] = $dateFin;

        $donnees = $repo->findJournal($code, $dateDebut, $dateFin);

        // Le subtitle
        $j= $repoJ->findOneBy(['code'=>$code]);

        $pdf = new Pdf($periode, $parametreRepo->findDonneesMutuelle(),'Journal', $j->getCode().'  '.$j->getIntitule());
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'A4');

        $pdf->journalTable($donnees);
        
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }  
    
    /**
     * @Route("/pdf/grand-livre/{poste}/{debut}/{fin}", name="pdf_livre")
     */
    public function pdfLivre($poste, $debut, $fin,ParametreRepository $parametreRepo, ArticleRepository $repo, CompteRepository $repoCompte)
    {
        $dateDebut = \DateTime::createFromFormat('dmY', $debut);
        $dateFin = \DateTime::createFromFormat('dmY', $fin);

        $periode['debut'] = $dateDebut;
        $periode['fin'] = $dateFin;

        if ($poste == "all") {
            $donnees = $repo->findGrandLivre($dateDebut, $dateFin);
            $subtitle = "Générale";
        } else {
            $compte = $repoCompte->findOneByPoste($poste);
            if ($compte)
                $donnees = $repo->findGrandLivreCompte($dateDebut, $dateFin, $compte);
            else 
                throw $this->createNotFoundException("Le compte numéro $poste n'existe pas");
            $subtitle = 'Auxilliaires';
        }

        $pdf = new Pdf($periode,$parametreRepo->findDonneesMutuelle(), 'Grand-livre des comptes', $subtitle);
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'A4');

        $pdf->livreTable($donnees);
        
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }  

    /**
     * @Route("/pdf/bilan", name="pdf_bilan")
     */
    public function pdfBilan(ConfigEtatFi $etatFi,ParametreRepository $parametreRepo, CompteRepository $repo, SessionInterface $session)
    {
        $exercice = $session->get('exercice'); 
        $periode = ['debut' => $exercice->getDateDebut(), 'fin' => $exercice->getDateFin()];

        // ANC
        $posteActifNonCourant = $etatFi->actifNonCourant();  
        $donneesA['anc']  = $this->getBilan($exercice, $repo, $posteActifNonCourant);
        // AC
        $posteActifCourant = $etatFi->actifCourant();  
        $donneesA['ac'] = $this->getBilan($exercice, $repo, $posteActifCourant);
        // CP
        $capitaux = $etatFi->capitauxPropres();
        $donneesP['cp'] = $this->getBilan($exercice, $repo, $capitaux);
        // PNC
        $passifsNonCourants = $etatFi->passifNonCourant();
        $donneesP['pnc'] = $this->getBilan($exercice, $repo, $passifsNonCourants);
        // PC
        $passifsCourants = $etatFi->passifCourant();
        $donneesP['pc'] = $this->getBilan($exercice, $repo, $passifsCourants);

        $pdf = new Pdf($periode, $parametreRepo->findDonneesMutuelle(),'Bilan actif', 'Document fin d\'exercice');
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'A4');
        $pdf->bilanActifTable($donneesA);

        $pdf->AddPage('P', 'A4');
        $pdf->bilanPassifTable($donneesP);
        
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /** 
     * Convert bilan poste into solde
     */
    private function getBilan($exercice, $repo, $groupes)
    {
        $anc = [];
        foreach ($groupes as $rubrique) {
            if (count($rubrique) == 4) {
                $anc[] = [ $rubrique[0], $rubrique[1], $repo->findSoldes($rubrique[2], $exercice), $repo->findSoldes($rubrique[3], $exercice)];
            } else {
                $anc[] = $rubrique;
            }
        }
        return $anc;
    }

    /**
     * @Route("/pdf/resultat", name="pdf_resultat")
     */
    public function pdfResultat(ConfigEtatFi $etatFi,ParametreRepository $parametreRepo, CompteRepository $repo, SessionInterface $session)
    {
        $exercice = $session->get('exercice'); 
        $periode = ['debut' => $exercice->getDateDebut(), 'fin' => $exercice->getDateFin()];
        
        // ca
        $chiffreAffaireNet = $etatFi->chiffreAffaireNet();  
        $donnees['ca']  = $this->getResultat($exercice, $repo, $chiffreAffaireNet);
        // pE
        $productionExploitation = $etatFi->productionExploitation();
        $donnees['pE']  = $this->getResultat($exercice, $repo, $productionExploitation);
        //cE
        $chargesExploitation = $etatFi->chargeExploitation();
        $donnees['cE']  = $this->getResultat($exercice, $repo, $chargesExploitation);
        // op
        $op = $etatFi->operationEnCommmun();
        $donnees['op']  = $this->getResultat($exercice, $repo, $op);
        // PF
        $pFinanciers = $etatFi->productionsFinanciers();
        $donnees['pf']  = $this->getResultat($exercice, $repo, $pFinanciers);
        // CF
        $cFinanciers = $etatFi->chargesFinanciers();
        $donnees['cf']  = $this->getResultat($exercice, $repo, $cFinanciers);
        // pe
        $pException = $etatFi->produitExceptionnel();
        $donnees['pe']  = $this->getResultat($exercice, $repo, $pException);
        // ce
        $cException = $etatFi->chargeExceptionnel();
        $donnees['ce']  = $this->getResultat($exercice, $repo, $cException);
        // im
        $impots = $etatFi->impots();
        $donnees['im']  = $this->getResultat($exercice, $repo, $impots);

        $pdf = new Pdf($periode,$parametreRepo->findDonneesMutuelle(), 'Compte de résultat', 'Document fin d\'exercice');
        $pdf->AliasNbPages();
        $pdf->AddPage('P', 'A4');
        $pdf->compteResultatTable($donnees);
        
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /** 
     * Convert resultat poste into solde
     */
    private function getResultat($exercice, $repo, $groupes)
    {
        $anc = [];
        foreach ($groupes as $rubrique) {
            if (count($rubrique) == 3) {
                $anc[] = [ $rubrique[0], $rubrique[1], $repo->findSoldes($rubrique[2], $exercice)];
            } else {
                $anc[] = $rubrique;
            }
        }
        return $anc;
    }

}
