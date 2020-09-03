<?php

namespace App\Service;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportExcel
{
    private $manager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
    }

    public function importPlanComptable($file)
    {
        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($file);

        $data = [];

        // A chaque feuille
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle(); // Nom feuille
            $data[$worksheetTitle] = [
                'columnNames' => [],
                'columnValues' => [],
            ];
            // A chaque ligne
            foreach ($worksheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
            
                if ($rowIndex > 1) { // On est superieur à la premiere ligne alors c'est un donnees
                    $data[$worksheetTitle]['columnValues'][$rowIndex] = [];
                }

                $cellIterator = $row->getCellIterator(); 

                $cellIterator->setIterateOnlyExistingCells(false); // Loop over all cells, even if it is not set
                foreach ($cellIterator as $cell) {
                    if ($rowIndex === 1) { // Si c'est le premier ligne alors c'est l'en-tete
                        $data[$worksheetTitle]['columnNames'][] = $cell->getCalculatedValue();
                    }
                    if ($rowIndex > 1) { // C'est un ligne de pac alors en enregistre
                        $data[$worksheetTitle]['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }
        }

        // Save the data
        return $this->saveIntoDatabase($data);
    }

    public function saveIntoDatabase($data = [])
    {
        $retour['hasError'] = false;
        $donnees = $data['Feuil1']['columnValues'];

        foreach ($donnees as $row => $donnee) {
            $compte = new Compte();
            // Tous les postes importés sont les rubriques
            $compte->setPosteRubrique($donnee[0]);
            $compte->setTitre($donnee[1]);
            $errors = $this->validator->validate($compte);
            if (0 === count($errors)) {
                $this->manager->persist($compte);
            } else {    
                $retour['hasError'] = true;
                $retour['ErrorMessages'][] = "Les informations de la ligne N° $row est invalide"; 
                foreach ($errors as $error) {
                    $retour['ErrorMessages'][] = $error->getMessage();
                }
                return $retour;
            }
        }
        $this->manager->flush(); 
        
        return $retour;
    }
}