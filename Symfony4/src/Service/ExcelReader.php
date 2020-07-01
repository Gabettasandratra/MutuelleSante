<?php

namespace App\Service;

use App\Entity\Pac;
use App\Entity\Adherent;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExcelReader
{
    private $manager;
    private $validator;
    private $exerciceRepo;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ExerciceRepository $exerciceRepo)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->exerciceRepo = $exerciceRepo;
    }

    public function savePacFromExcel(Adherent $adherent, $file)
    {
        $reader = new ReaderXlsx();
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
        return $this->saveIntoDatabase($data, $adherent);
    }

    public function saveIntoDatabase($data = [], Adherent $adherent)
    {
        $retour['hasError'] = false;
        $donnees = $data['Beneficiaire']['columnValues'];

        // update the compte cotisation / if nouveau ++
        $currentExercice = $this->exerciceRepo->findCurrent();
        $currentCompteCotisation = $adherent->getCompteCotisation($currentExercice);

        foreach ($donnees as $row => $donnee) {
            $pac = new Pac();
            $pac->setCodeMutuelle($donnee[0]);
            $pac->setNom($donnee[1]);
            $pac->setPrenom($donnee[2]);
            $pac->setSexe($donnee[3]);
            $pac->setDateNaissance(\DateTime::createFromFormat('d/m/Y', $donnee[4]));
            $pac->setCin($donnee[5]);
            $pac->setParente($donnee[6]);
            $pac->setDateEntrer(\DateTime::createFromFormat('d/m/Y', $donnee[7]));

            $pac->setCreatedAt(new \DateTime());
            $pac->setIsSortie(false);
            $pac->setPhoto("http://placehold.it/100x100");

            $errors = $this->validator->validate($pac);

            if (0 === count($errors)) {
                // test if the pac is nouveau or ancien
                $isNouveau = $pac->isNouveau($currentExercice);
                if ($isNouveau) {
                $currentCompteCotisation->incrementNouveau();
                } else {
                $currentCompteCotisation->incrementAncien();
                }

                $adherent->addPac($pac);
                $this->manager->persist($pac);
            } else {    
                $retour['hasError'] = true;
                $retour['ErrorMessages'][] = "Les informations de la ligne N° $row est invalide"; 
                foreach ($errors as $error) {
                    $retour['ErrorMessages'][] = $error->getMessage();
                }
                return $retour;
            }
        }
        $this->manager->persist($adherent);  
        $this->manager->persist($currentCompteCotisation);  
        $this->manager->flush(); 
        
        return $retour;
    }
    
}