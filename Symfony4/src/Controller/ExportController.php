<?php

namespace App\Controller;

use App\Entity\Adherent;
use App\Service\ExportExcel;
use App\Entity\Remboursement;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportController extends AbstractController
{
    /**
     * @Route("/export/vider", name="export_vider")
     */
    public function vider(ExportExcel $exportService)
    {
        $filename = $exportService->exportEtatFinanciere();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

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

    
}
