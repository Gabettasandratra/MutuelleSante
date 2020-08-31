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
        $parametres[] = new Parametre('label_cotisation', 'Cotisation {a} | {c}');
        $parametres[] = new Parametre('analytique_cotisation', 'COT');
        $parametres[] = new Parametre('compte_prestation');
        $parametres[] = new Parametre('label_prestation', 'Remboursement {a} | {c}');
        $parametres[] = new Parametre('analytique_prestation', 'REMB');
        $parametres[] = new Parametre('percent_prestation', 1);
        $parametres[] = new Parametre('plafond_prestation', 2);
        $parametres[] = new Parametre('percent_rembourse_prestation', 0.6);
        $parametres[] = new Parametre('compte_dette_prestation');
        
        
        $soins = new Parametre('soins_prestation');
        $soins->setList([
            'DENTAIRE' => 'Soins dentaires',
            'AUTRES' => 'Autres soins'
        ]); 
        $parametres[] = $soins;

        $analytiques = new Parametre('plan_analytique');
        $analytiques->setList([
            'COM' => 'Commun',
            'BUR' => 'Bureau',
        ]); 
        
        $parametres[] = $analytiques;
        $parametres[] = new Parametre('code_analytique_cong', 'CONG-{n}');


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
                ->setCategorie('COMPTES DE BILAN') // Categorie
                ->setType(false) // Passif
                ->setClasse('1-COMPTES DE CAPITAUX') // Classe
                ->setPoste('110000')
                ->setTitre('Report à nouveau (solde créditeur)')
                ->setNote('Les bénéfices de l\'exercice précedent');
        $ran119 = new Compte();
        $ran119->setIsTresor(false) // Pas de trésorerie
                ->setCategorie('COMPTES DE BILAN') // Categorie
                ->setType(false) // Passif
                ->setClasse('1-COMPTES DE CAPITAUX') // Classe
                ->setPoste('119000')
                ->setTitre('Report à nouveau (solde débiteur)')
                ->setNote('Les pertes de l\'exercice précedent');
        
        // Résultat de l'exercice
        $res120 = new Compte();
        $res120->setIsTresor(false) // Pas de trésorerie
                ->setCategorie('COMPTES DE BILAN') // Categorie
                ->setType(false) // Passif
                ->setClasse('1-COMPTES DE CAPITAUX') // Classe
                ->setPoste('120000')
                ->setTitre('Résultat de l\'exercice (bénéfice)')
                ->setNote('Les bénéfices de l\'exercice à la clôture');
        $res129 = new Compte();
        $res129->setIsTresor(false) // Pas de trésorerie
                ->setCategorie('COMPTES DE BILAN') // Categorie
                ->setType(false) // Passif
                ->setClasse('1-COMPTES DE CAPITAUX') // Classe
                ->setPoste('129000')
                ->setTitre('Résultat de l\'exercice (perte)')
                ->setNote('Les pertes de l\'exercice à la clôture');
        $this->manager->persist($ran110);  
        $this->manager->persist($ran119);  
        $this->manager->persist($res120);  
        $this->manager->persist($res129);  
        $this->manager->flush();
    }
}