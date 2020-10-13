<?php

namespace App\Service;

use App\Entity\Pac;
use App\Entity\Adherent;
use App\Entity\Prestation;
use App\Repository\PacRepository;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Validator\ConstraintViolation;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExcelReader
{
    private $manager;
    private $validator;
    private $exerciceRepo;
    private $pacRepo;
    private $user;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ExerciceRepository $exerciceRepo, PacRepository $pacRepo, TokenStorageInterface $tokenStorage)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->exerciceRepo = $exerciceRepo;
        $this->pacRepo = $pacRepo;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function savePacFromExcel(Adherent $adherent, $file)
    {
        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($file);

        $data = $this->getData($spreadsheet);

        // Save the data
        return $this->saveIntoDatabase($data, $adherent);
    }

    public function saveIntoDatabase($data = [], Adherent $adherent)
    {
        $retour['hasError'] = false;

        // Les sheets
        $sheets = array_keys($data);
        $donnees = $data[$sheets[0]]['columnValues'];

        // update the compte cotisation / if nouveau ++
        $currentExercice = $this->exerciceRepo->findCurrent();
        $currentCompteCotisation = $adherent->getCompteCotisation($currentExercice);
        // Code généré (pour les nouveau importer)
        $g = $this->pacRepo->generateCode($adherent);

        foreach ($donnees as $row => $donnee) {
            $pac = new Pac();
            if ($donnee[0]) {
                $pac->setCodeMutuelle($donnee[0]);
            } else {          
                $pac->setCodeMutuelle($g);
                $g++;
            }
            $pac->setNom($donnee[1]);
            $pac->setPrenom($donnee[2]);
            if ($donnee[3][0] == 'M')
                $pac->setSexe('Masculin');
            else
                $pac->setSexe('Feminin');

            $pac->setDateNaissance($this->getDateTimeFromExcel( $donnee[4]));
            $pac->setCin($donnee[5]);
            if($donnee[6] !== null) $pac->setParente($donnee[6]);
            $pac->setDateEntrer($this->getDateTimeFromExcel( $donnee[7]));

            $pac->setCreatedAt(new \DateTime());
            $pac->setIsSortie(false);
            $pac->setPhoto("assets/images/users/profile.png");

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
                    //$a = new ConstraintViolation(); 
                    //$a->getInvalidValue
                    $retour['ErrorMessages'][] = $error->getMessage().' : '.$error->getInvalidValue();
                }
                return $retour;
            }
        }
        $this->manager->persist($adherent);  
        $this->manager->persist($currentCompteCotisation);  
        $this->manager->flush(); 
        
        return $retour;
    }

    /* Import donnees de la decompte de prestation */
    public function saveDecompte($file)
    {
        $reader = new ReaderXlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($file);

        $data = $this->getData();

        $retour['hasError'] = false;
        // Les sheets
        $sheets = array_keys($data);
        $donnees = $data[$sheets[0]]['columnValues'];

        $exercice = $this->exerciceRepo->findCurrent();
        $generatedNumero = $repositoryPrestation->generateNumero($pac->getAdherent(), $exercice);// Le numero de décompte

        foreach ($donnees as $row => $donnee) {
            // Recherche du beneficiaire
            $pac = $this->pacRepo->findByCodeMutuelle($donnee[0]);

            $prestation = new Prestation();
            $prestation->setPac($pac);
            $prestation->setDate($this->getDateTimeFromExcel($donnee[1]));
            $prestation->setDesignation($donnee[2]);
            $prestation->setFrais((float)$donnee[3]);
            $prestation->setRembourse((float)$donnee[4]);
            //le status
            if ($prestation->getRembourse() > 0 )
                $prestation->setStatus(1);
            else
                $prestation->setStatus(-1);
            $prestation->setPrestataire($donnee[5]);
            $prestation->setFacture($donnee[6]);
            $prestation->setUser($this->user);
            $prestation->setDecompte($generatedNumero);

            $errors = $this->validator->validate($prestation);

            if (0 === count($errors)) {
                $this->manager->persist($prestation);
            } else {    
                $retour['hasError'] = true;
                $retour['ErrorMessages'][] = "Les informations de la ligne N° $row est invalide"; 
                foreach ($errors as $error) 
                    $retour['ErrorMessages'][] = $error->getMessage(). ' ('.$error->getInvalidValue().')';    
                return $retour;
            }
        }
        $this->manager->persist($adherent);  
        $this->manager->persist($currentCompteCotisation);  
        $this->manager->flush(); 

    }

    private function getData($spreadsheet) {
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
        return $data;
    }

    public function getDateTimeFromExcel($data)
    {
        if(strpos($data, "/"))
        {
            return \DateTime::createFromFormat('d/m/Y', $data);
        } else {
            return Date::excelToDateTimeObject($data);
        }
    }
    
}