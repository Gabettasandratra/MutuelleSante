<?php

namespace App\Controller;

use Fpdf\Fpdf;
use App\Pdf\Pdf;
use App\Entity\Adherent;
use App\Pdf\PDFMutuelle;
use App\Repository\ArticleRepository;
use App\Repository\AdherentRepository;
use App\Repository\PrestationRepository;
use App\Repository\RemboursementRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CompteCotisationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function home()
    {
        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(AdherentRepository $repo, PrestationRepository $repoPre, SessionInterface $session)
    {
        $exercice = $session->get('exercice');
        $congregations = array_reverse($repo->findEvolutionCongregation($exercice->getAnnee()), true);         
        $beneficiaires = array_reverse($repo->findEvolutionBeneficiaire($exercice->getAnnee()), true);         
        
        $json_congregation = json_encode([
                                'label' => array_keys($congregations),
                                'series' => array_values($congregations)
                            ]);
        $json_beneficiaire = json_encode([
                                'label' => array_keys($beneficiaires),
                                'series' => array_values($beneficiaires)
                            ]);
        
        $montantPrestations = $repoPre->findMontantPayedEachMonth($exercice->getAnnee());
        
        $m_remb = array();
        foreach ($montantPrestations as $prestation) {
            $m_remb[] = (float) $prestation['m_remb'];
        }

        $m_frais = array();
        foreach ($montantPrestations as $prestation) {
            $m_frais[] = (float) $prestation['m_frais'];
        }


        $json_prestations = json_encode([
            'label' => ['Jan','Fev','Mar','Avr','Mai','Jui','Juil','Août','Sep','Oct','Nov','Dec'],
            'series' => [$m_frais, $m_remb]
        ]);

        $percentSoins = $repoPre->findPercentActe($exercice->getAnnee());
        $json_soins = json_encode([
            'label' => array_keys($percentSoins),
            'series' => array_values($percentSoins)
        ]);
        
        return $this->render('dashboard/index.html.twig', [
            'congregations' => $json_congregation,
            'beneficiaires' => $json_beneficiaire,
            'prestations' => $json_prestations,
            'soins' => $json_soins,
        ]);
    }

    /**
     * @Route("/dashboard/pdf/cong", name="dashboard_test_congregation")
     */
    public function testPdfCongregation(SessionInterface $session, AdherentRepository $repo)
    {
        // Get list congregations
        $exercice = $session->get('exercice');
        $congs = $repo->findByExercice($exercice);

        // En tete table
        $header = ['N°', 'Congrégation', 'Date adhésion', 'Adresse', 'Tél', 'Email'];
        // Width table
        $w = [10, 60, 30, 50, 50, 60];

        // Pdf file
        $exercice = $session->get('exercice'); 
        $periode = ['debut' => $exercice->getDateDebut(), 'fin' => $exercice->getDateFin()];

        $pdf = new PDFMutuelle($periode, "Congrégation");
        $pdf->AliasNbPages();
        $pdf->AddPage('L', 'A4');

        // Write header
        $pdf->SetFont('Arial','B',9);
        for($i=0;$i<count($header);$i++)
            $pdf->Cell($w[$i],7,$header[$i],1,0,'C');
        $pdf->Ln();

        $pdf->SetFont('Arial','',9);// 
        foreach($congs as $c)
        {
            $pdf->Cell($w[0],6,$c->getNumero(),'LR');
            $pdf->Cell($w[1],6,$c->getNom(),'LR');
            $pdf->Cell($w[2],6,$c->getDateInscription()->format('d/m/Y'),'LR',0,'R');
            $pdf->Cell($w[3],6,$c->getAdresse(),'LR',0,'R');
            $pdf->Cell($w[4],6,$c->getTelephone(),'LR',0,'R');
            $pdf->Cell($w[5],6,$c->getEmail(),'LR',0,'R');
            $pdf->Ln();
        }
        $pdf->Cell(array_sum($w),0,'','T'); // close the table

        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /**
     * @Route("/export/pdf/beneficiaire/{id}", name="dashboard_pdf_beneficiaire")
     */
    public function printPdfBeneficiaire(Adherent $adherent,SessionInterface $session, AdherentRepository $repo)
    {
        // Get list congregations
        $exercice = $session->get('exercice');
        $periode = ['debut' => $exercice->getDateDebut(), 'fin' => $exercice->getDateFin()];
        // En tete table
        $header = ['N° Matri', 'Nom et Prénom', 'S', 'Date Nais', 'Tél', 'CIN', 'Date Entré', 'Fonction'];    
        // Width table
        $w = [20, 70, 10, 30, 40, 40, 30, 30];

        // Pdf file
        $pdf = new PDFMutuelle($periode, "Beneficiaires");
        $pdf->AliasNbPages(); 

        $ancs = $repo->findAncien($exercice, $adherent);           
        $pdf->AddPage('L', 'A4');

        // En  tete
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(100,8,'LISTE DES ANCIENS MEMBRES',0,0,'C');
        $pdf->Cell(70,8,$adherent->getNom(),0,0,'C');
        $pdf->Cell(100,8,'Anneé '.$exercice->getAnnee(),0,0,'R');
        $pdf->Ln();
        // Write header
        $pdf->SetFont('Arial','B',12);
        for($i=0;$i<count($header);$i++)
            $pdf->Cell($w[$i],7,$header[$i],1,0,'C');
        $pdf->Ln();


        $pdf->SetFont('Arial','',11);// 
        foreach($ancs as $ancien)
        {
            $pdf->Cell($w[0],6,$ancien->getCodeMutuelle(),'LR',0,'C');
            $pdf->Cell($w[1],6,$ancien->getNomComplet(),'LR');
            $pdf->Cell($w[2],6,$ancien->getSexe()[0],'LR',0,'C');
            $pdf->Cell($w[3],6,$ancien->getDateNaissance()->format('d/m/Y'),'LR',0,'C');
            $pdf->Cell($w[4],6,$ancien->getTel(),'LR',0,'C');
            $pdf->Cell($w[5],6,$ancien->getCin(),'LR',0,'C');
            $pdf->Cell($w[6],6,$ancien->getDateEntrer()->format('d/m/Y'),'LR',0,'C');
            $pdf->Cell($w[7],6,$ancien->getParente(),'LR',0,'C');
            $pdf->Ln();
        }
        $pdf->Cell(array_sum($w),0,'','T'); // close the table
    

        $ancs = $repo->findNouveau($exercice, $adherent);

        $pdf->AddPage('L', 'A4');
        // En  tete
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(100,8,'LISTE DES NOUVEAUX MEMBRES',0,0,'C');
        $pdf->Cell(70,8,$adherent->getNom(),0,0,'C');
        $pdf->Cell(100,8,'Anneé '.$exercice->getAnnee(),0,0,'R');
        $pdf->Ln();

        // Write header
        $pdf->SetFont('Arial','B',12);
        for($i=0;$i<count($header);$i++)
            $pdf->Cell($w[$i],7,$header[$i],1,0,'C');
        $pdf->Ln();


        $pdf->SetFont('Arial','',11);// 
        foreach($ancs as $ancien)
        {
            $pdf->Cell($w[0],6,$ancien->getCodeMutuelle(),'LR',0,'C');
            $pdf->Cell($w[1],6,$ancien->getNomComplet(),'LR');
            $pdf->Cell($w[2],6,$ancien->getSexe()[0],'LR',0,'C');
            $pdf->Cell($w[3],6,$ancien->getDateNaissance()->format('d/m/Y'),'LR',0,'C');
            $pdf->Cell($w[4],6,$ancien->getTel(),'LR',0,'C');
            $pdf->Cell($w[5],6,$ancien->getCin(),'LR',0,'C');
            $pdf->Cell($w[6],6,$ancien->getDateEntrer()->format('d/m/Y'),'LR',0,'C');
            $pdf->Cell($w[7],6,$ancien->getParente(),'LR',0,'C');
            $pdf->Ln();
        }
        $pdf->Cell(array_sum($w),0,'','T'); // close the table
    
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /**
     * @Route("/export/pdf/cotisation", name="dashboard_pdf_cotisation")
     */
    public function printPdfCotisation(SessionInterface $session, CompteCotisationRepository $repo)
    {
        $exercice = $session->get('exercice');
        $header = ['N°', 'Congrégation', 'Anciens', 'Nouveaux', 'Montant due', 'Montant percue', 'Reste à payé'];   
        
        $pdf = new PDFMutuelle();
        $pdf->AliasNbPages();                     
        $pdf->AddPage('L', 'A4');

        // En  tete
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(100,8,'COTISATION DES CONGREGATIONS',0,0,'C');
        $pdf->Cell(160,8,'Anneé '.$exercice->getAnnee(),0,0,'R');
        $pdf->Ln(); 
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(260,7,'Ancien membre: '.number_format($exercice->getCotAncien(),0, ","," ").' Ar',0,0,'R');
        $pdf->Ln(); 
        $pdf->Cell(260,7,'Nouveau membre: '.number_format($exercice->getCotNouveau(),0, ","," ").' Ar',0,0,'R');
        $pdf->Ln(); 
        // Colonne des tables
        $w = [10, 80, 25, 25, 40, 40, 40];
        $pdf->SetFont('Arial','B',12);
        for($i=0;$i<count($header);$i++)
            $pdf->Cell($w[$i],7,$header[$i],1,0,'C');
        $pdf->Ln();
        // Data
        $lines = $repo->findByExercice($exercice);
        // Line
        $pdf->SetFont('Arial','',11);
        // Totaux
        $tD = 0; $tP = 0; $tR = 0;
        foreach($lines as $line)
        {
            $pdf->Cell($w[0],6,$line->getAdherent()->getNumero(),'LR',0,'C');
            $pdf->Cell($w[1],6,$line->getAdherent()->getNom(),'LR');
            $pdf->Cell($w[2],6,$line->getNouveau(),'LR',0,'C');
            $pdf->Cell($w[3],6,$line->getAncien(),'LR',0,'C');
            $pdf->Cell($w[4],6,number_format($line->getDue(), 2, ",", " "),'LR',0,'C');
            $pdf->Cell($w[5],6,number_format($line->getPaye(), 2, ",", " "),'LR',0,'C');
            $pdf->Cell($w[6],6,number_format($line->getReste(), 2, ",", " "),'LR',0,'C');
            $pdf->Ln();
            $tD += $line->getDue(); $tP += $line->getPaye(); $tR += $line->getReste();
        }
        $pdf->SetFont('Arial','B',11);        
        $pdf->Cell(140,6, 'Totaux',1,0,'R');
        $pdf->Cell(40,6, number_format($tD, 2, ",", " "),1,0,'C');
        $pdf->Cell(40,6, number_format($tP, 2, ",", " "),1,0,'C');
        $pdf->Cell(40,6, number_format($tR, 2, ",", " "),1,0,'C');

        // PAGE DES HISTORIQUES
        $pdf->AddPage('L', 'A4');
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(100,8,'HISTORIQUE DE PAIEMENT COTISATION',0,0,'C');
        $pdf->Cell(160,8,'Anneé '.$exercice->getAnnee(),0,0,'R');
        $pdf->Ln();
        // Colonne des tables
        $w = [10, 80, 30, 40, 40, 80];
        $header = ['N°', 'Congrégation', 'Date Vers', 'Montant Vers', 'Référence', 'Remarque']; 
        $pdf->SetFont('Arial','B',12);
        for($i=0;$i<count($header);$i++)
            $pdf->Cell($w[$i],7,$header[$i],1,0,'C');
        $pdf->Ln();
        $pdf->SetFont('Arial','',11);
        // line
        $tM = 0;
        foreach ($lines as $line) {
            foreach ($line->getHistoriqueCotisations() as $paiement) {
                $pdf->Cell($w[0],6,$line->getAdherent()->getNumero(),'LR',0,'C');
                $pdf->Cell($w[1],6,$line->getAdherent()->getNom(),'LR');
                $pdf->Cell($w[2],6,$paiement->getDatePaiement()->format('d/m/Y'),'LR',0,'C');
                $pdf->Cell($w[3],6,number_format($paiement->getMontant(), 2, ",", " "),'LR',0,'C');
                $pdf->Cell($w[4],6,$paiement->getReference(),'LR',0,'C');
                $pdf->Cell($w[5],6,$paiement->getRemarque(),'LR',0, 'C');
                $pdf->Ln();
                $tM += $paiement->getMontant();
            }
        }
        $pdf->SetFont('Arial','B',11);        
        $pdf->Cell(120,6, 'Totaux',1,0,'R');
        $pdf->Cell(40,6, number_format($tM, 2, ",", " "),1,0,'C');
        $pdf->Cell(120,6,'','T');

        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }

    /**
     * @Route("/export/pdf/remboursement", name="dashboard_pdf_remb")
     */
    public function printPdfRemboursement(SessionInterface $session, AdherentRepository $repo, CompteCotisationRepository $repoCot)
    {
        $exercice = $session->get('exercice');    
        $pdf = new PDFMutuelle();
        $pdf->AliasNbPages();                     
        $pdf->AddPage('L', 'A4');

        // EN TETE
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(140,8,'REMBOURSEMENT DES CONGREGATIONS',0,0,'C');
        $pdf->Cell(100,8,'Anneé '.$exercice->getAnnee(),0,0,'R');
        $pdf->Ln(); 

        // En tete Table
        $w = [10, 80, 30, 40, 40, 40];
        $header = ['N°', 'Congrégation', 'Date', 'Montant', 'Banque', 'Référence'];
        $pdf->SetFont('Arial','B',12);
        for($i=0;$i<count($header);$i++)
            $pdf->Cell($w[$i],7,$header[$i],1,0,'C');
        $pdf->Ln();

        // Corps du table
        
        $congs = $repo->findAll();
        $tR = 0;
        foreach($congs as $cong) {
            $rembs = $cong->getRemboursementByExercice($exercice);
            $tRp = 0;
            foreach ($rembs as $line) {
                $pdf->SetFont('Arial','',11);
                $pdf->Cell($w[0],6,$cong->getNumero(),'LR',0,'C');
                $pdf->Cell($w[1],6,$cong->getNom(),'LR');
                $pdf->Cell($w[2],6,$line->getDate()->format('d/m/Y'),'LR',0,'C');
                $pdf->Cell($w[3],6,number_format($line->getMontant(), 2, ",", " "),'LR',0,'C');
                $pdf->Cell($w[4],6,$line->getTresorerie()->getTitre(),'LR',0,'C');
                $pdf->Cell($w[5],6,$line->getReference(),'LR',0,'C');
                $pdf->Ln();
                $tRp += $line->getMontant();
            }
            $pdf->SetFont('Arial','B',11);        
            $pdf->Cell(120,6, 'Sous-totaux N°'.$cong->getNumero(),1,0,'R');
            $pdf->Cell(40,6, number_format($tRp, 2, ",", " "),1,0,'C');

            // Recupere le plafond
            $cCot = $repoCot->findCompteCotisation($cong, $exercice);

            $pdf->Cell(80,6,'Remb plafond : '.number_format($cCot->getPaye()*2, 2, ",", " "),1,0,'R');
            $pdf->Ln();
            $tR += $tRp;
        } 

        $pdf->SetFont('Arial','B',11);        
        $pdf->Cell(120,6, 'Totaux',1,0,'R');
        $pdf->Cell(40,6, number_format($tR, 2, ",", " "),1,0,'C');

        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }
}
