<?php

namespace App\Controller;

use App\Service\ExportExcel;
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
        $filename = $exportService->writeListeAdherent();

        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }
    
    /**
     * @Route("/export/{filename}", name="export")
     */
    public function index($filename, ExportExcel $exportService)
    {
        //$filename = $exportService->writeListeAdherent();
        $filePath = $this->getParameter('export_temp_root_directory').'/'.$filename;

        return $this->file($filePath, $filename, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    
}
