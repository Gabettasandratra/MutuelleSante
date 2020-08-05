<?php

namespace App\Controller;

use Fpdf\Fpdf;
use App\Repository\AdherentRepository;
use App\Repository\PrestationRepository;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/dashboard/test", name="dashboard_test")
     */
    public function testPdf()
    {
        $pdf = new Fpdf(); 
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);// 
        $pdf->Cell(0,10,'Hello World!',1,0,'C',false,'https://www.plus2net.com');
        return new Response(
            $pdf->Output('file.pdf', 'I'),
            Response::HTTP_OK,
            array('content-type' => 'application/pdf')
        );
    }
}
