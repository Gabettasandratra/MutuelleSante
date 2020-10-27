<?php

namespace App\Service;

use App\Entity\Compte;
use App\Entity\Parametre;
use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParametreService
{
    private $manager;
    private $validator;
    private $exerciceRepo;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, ParametreRepository $parametreRepo)
    {
        $this->manager = $entityManager;
        $this->validator = $validator;
        $this->parametreRepo = $parametreRepo;
    }

    public function initialize()
    {
        // Parametres comptable 
        $parametres = [];
        
        $parametres[] = new Parametre('compte_cotisation');
        $parametres[] = new Parametre('label_cotisation', 'Cot {a} | {c}');
        $parametres[] = new Parametre('compte_prestation');
        $parametres[] = new Parametre('label_prestation', 'Remb {a} | {c}');
        $parametres[] = new Parametre('percent_prestation', 1);
        $parametres[] = new Parametre('plafond_prestation', 2);
        $parametres[] = new Parametre('percent_rembourse_prestation', 0.6);
        $parametres[] = new Parametre('percent_rembourse_prestation_plafond', 0.25);
        $parametres[] = new Parametre('compte_dette_prestation');    
        $parametres[] = new Parametre('soins_prestation');
        $parametres[] = new Parametre('nom_mutuelle', 'Mutuelle Santé');
        $parametres[] = new Parametre('adresse_mutuelle', 'adresse');
        $parametres[] = new Parametre('contact_mutuelle', 'telephone');
        $parametres[] = new Parametre('email_mutuelle', 'email');

        foreach ($parametres as $parametre) {
            $this->manager->persist($parametre);  
        }
        $this->manager->flush();

        $this->initializeCompte();
    }

    public function getParametre($nom)
    {
        return $this->parametreRepo->findOneByNom($nom)->getValue();
    }

    /* Initialize all account utils */
    private function initializeCompte()
    {
        // Les report à nouveau
        $ran110 = new Compte();
        $ran110->setIsTresor(false) // Pas de trésorerie
                ->setPoste('110000')
                ->setTitre('Report à nouveau (solde créditeur)');
        $ran119 = new Compte();
        $ran119->setIsTresor(false) // Pas de trésorerie
                ->setPoste('119000')
                ->setTitre('Report à nouveau (solde débiteur)');     
        // Résultat de l'exercice
        $res120 = new Compte();
        $res120->setIsTresor(false) // Pas de trésorerie
                ->setPoste('120000')
                ->setTitre('Résultat de l\'exercice (bénéfice)');
        $res129 = new Compte();
        $res129->setIsTresor(false) // Pas de trésorerie
                ->setPoste('129000')
                ->setTitre('Résultat de l\'exercice (perte)');
        $this->manager->persist($ran110);  
        $this->manager->persist($ran119);  
        $this->manager->persist($res120);  
        $this->manager->persist($res129);  
        $this->manager->flush();
    }
}