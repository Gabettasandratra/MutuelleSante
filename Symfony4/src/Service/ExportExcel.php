<?php

namespace App\Service;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExportExcel
{
    private $manager;
    private $filesystem;
    private $export_dir_root;
    private $export_dir;
    private $session;

    public function __construct(EntityManagerInterface $manager, Filesystem $filesystem, ParameterBagInterface $params, SessionInterface $session)
    {
        $this->manager = $manager;
        $this->filesystem = $filesystem;
        $this->export_dir_root = $params->get('export_temp_root_directory');
        $this->export_dir = $params->get('export_temp_directory');
        $this->session = $session;
    }

    public function writeListeAdherent()
    {
        $exercice = $this->session->get('exercice');

        /* Get list of all adhérents */ 
        $congregations = $this->manager->getRepository(Adherent::class)->findByExercice($exercice);

        $spreadsheet = new Spreadsheet();
        // Feuille 1
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Congrégations');

        
        $headers = ['N°', 'Congrégation', 'Date d\'inscription', 'Adresse', 'Tél', 'Email'];
        
        $this->writeHeaders($sheet, $headers);
        
        // Writes values
        $row = 1;
        foreach ($congregations as $congregation) {
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $congregation->getNumero());
            $sheet->setCellValueByColumnAndRow(2, $row, $congregation->getNom());
            $sheet->setCellValueByColumnAndRow(3, $row, $congregation->getDateInscription()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(4, $row, $congregation->getAdresse());
            $sheet->setCellValueByColumnAndRow(5, $row, $congregation->getTelephone());
            $sheet->setCellValueByColumnAndRow(6, $row, $congregation->getEmail());
        }

        $this->autoSizeColumns($spreadsheet);

        $filename = date('dmY_Hi') .'-congrégations.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
        
    }

    private function saveTemp(Spreadsheet $spreadsheet, $filename)
    {
        $writer = new WriterXlsx($spreadsheet);

        $this->filesystem->remove($this->export_dir); 
        $this->filesystem->mkdir($this->export_dir); 
        $writer->save($this->export_dir_root.'/'.$filename);

        return $filename;
    }

    private function writeHeaders($sheet, $headers, $row = 1)
    {
        // Writes headers
        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow(++$i, $row, $header);
        }
        $style = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ]
        ];
        $sheet->getStyle(Coordinate::stringFromColumnIndex(1).'1:'.Coordinate::stringFromColumnIndex(count($headers)).'1')
              ->applyFromArray($style)
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('066885');
    }

    private function autoSizeColumns(Spreadsheet $spreadsheet)
    {
        // Autosize all columns
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));
            $sheet = $spreadsheet->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            }
        }

        return $spreadsheet;
    }
}