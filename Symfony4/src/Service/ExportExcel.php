<?php

namespace App\Service;

use App\Entity\Compte;
use App\Entity\Article;
use App\Entity\Adherent;
use App\Entity\Remboursement;
use App\Entity\CompteCotisation;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\Filesystem\Filesystem;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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

    /*
     * Recupere la liste de tous les adherents avec quelques infos 
     */
    public function getListeAdherent()
    {
        $exercice = $this->session->get('exercice');

        /* Get list of all adhérents */ 
        $congregations = $this->manager->getRepository(Adherent::class)->findByExercice($exercice);

        $spreadsheet = new Spreadsheet();
        // Feuille 1
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Congrégations '. $exercice->getAnnee());
  
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
        $this->addBorder($sheet, $headers, $row);
        $this->autoSizeColumns($spreadsheet);

        $filename = 'Liste congrégations_'. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Recupere la liste des anciens et nouveaux bénéficiaires
     */
    public function getListeBeneficiaire(Adherent $adherent)
    {
        $exercice = $this->session->get('exercice');
        $spreadsheet = new Spreadsheet();

        /* LISTE DES ANCIENS */ 
        $anciens = $this->manager->getRepository(Adherent::class)->findAncien($exercice, $adherent);
        //$sheet1 = new Worksheet($spreadsheet, 'Ancien bénéficiaire '. $exercice->getAnnee());
        //$spreadsheet->addSheet($sheet1, 0);

        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ancien bénéficiaire '. $exercice->getAnnee());
        $this->saveSheetBeneficiare($sheet1, $anciens);
        
        /* LISTE DES NOUVEAUX */ 
        $nouveaux = $this->manager->getRepository(Adherent::class)->findNouveau($exercice, $adherent);
        $sheet2 = new Worksheet($spreadsheet, 'Nouveau bénéficiaire '. $exercice->getAnnee());
        $spreadsheet->addSheet($sheet2, 1);
        $this->saveSheetBeneficiare($sheet2, $nouveaux);
        $this->autoSizeColumns($spreadsheet);

        $filename = 'Liste bénéficiares-'. $adherent->getNom() .'_'. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Recupere la liste de tous les paiements de cotisations 
     */
    public function getInfosCotisation()
    {
        $exercice = $this->session->get('exercice');
        /* Get list of compte cotisation */ 
        $compteCotisations = $this->manager->getRepository(CompteCotisation::class)->findByExercice($exercice);

        $spreadsheet = new Spreadsheet();

        /* FEUILLES INFOS SUR LES CONGREGATIONS */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cotisations '. $exercice->getAnnee());
        $headers = ['N°', 'Congrégation', 'Ancien', 'Nouveau', 'Cotisation due', 'Cotisation percue', 'Reste à payé']; 
        $this->writeHeaders($sheet, $headers);
        // Writes values
        $row = 1;
        foreach ($compteCotisations as $compteCotisation) {
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $compteCotisation->getAdherent()->getNumero());
            $sheet->setCellValueByColumnAndRow(2, $row, $compteCotisation->getAdherent()->getNom());
            $sheet->setCellValueByColumnAndRow(3, $row, $compteCotisation->getNouveau());
            $sheet->setCellValueByColumnAndRow(4, $row, $compteCotisation->getAncien());
            $sheet->setCellValueByColumnAndRow(5, $row, $compteCotisation->getDue())
                    ->getStyle('E'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(6, $row, $compteCotisation->getPaye())
                    ->getStyle('F'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(7, $row, $compteCotisation->getReste())
                    ->getStyle('G'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }
        $this->addBorder($sheet, $headers, $row);

        /* FEUILLES SUR LES HISTORIQUES DE PAIEMENT COTISATION */
        $sheet2 = new Worksheet($spreadsheet, 'Historique des paiements '. $exercice->getAnnee());
        $spreadsheet->addSheet($sheet2, 1);
        $headers = ['N°', 'Congrégation', 'Date versement', 'Montant', 'Trésorerie', 'Référence', 'Note']; 
        $this->writeHeaders($sheet2, $headers);
        $row = 1;
        foreach ($compteCotisations as $compteCotisation) {
            foreach ($compteCotisation->getHistoriqueCotisations() as $historique) {
                $row++;
                $sheet2->setCellValueByColumnAndRow(1, $row, $compteCotisation->getAdherent()->getNumero());
                $sheet2->setCellValueByColumnAndRow(2, $row, $compteCotisation->getAdherent()->getNom());
                $sheet2->setCellValueByColumnAndRow(3, $row, $historique->getDatePaiement()->format('d/m/Y'));
                $sheet2->setCellValueByColumnAndRow(4, $row, $historique->getMontant())
                        ->getStyle('D'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet2->setCellValueByColumnAndRow(5, $row, $historique->getTresorerie()->getTitre());
                $sheet2->setCellValueByColumnAndRow(6, $row, $historique->getReference());
                $sheet2->setCellValueByColumnAndRow(7, $row, $historique->getRemarque());
            }
        }
        $this->addBorder($sheet2, $headers, $row);
        $this->autoSizeColumns($spreadsheet);
        $filename = 'Etats cotisations_'. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Recupere la liste de tous les remboursements d'une exercice
     */
    public function getRemboursement()
    {
        $exercice = $this->session->get('exercice');
        /* Get list of all adhérents */ 
        $remboursements = $this->manager->getRepository(Remboursement::class)->findByExercice($exercice);

        $spreadsheet = new Spreadsheet();
        // Feuille 1
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Remboursements '. $exercice->getAnnee());
  
        $headers = ['N°', 'Congrégation', 'Date', 'Montant', 'Trésorerie', 'Référence', 'Note'];
        $this->writeHeaders($sheet, $headers);
        
        // Writes values
        $row = 1;
        foreach ($remboursements as $remboursement) {
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $remboursement->getAdherent()->getNumero());
            $sheet->setCellValueByColumnAndRow(2, $row, $remboursement->getAdherent()->getNom());
            $sheet->setCellValueByColumnAndRow(3, $row, $remboursement->getDate()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(4, $row, $remboursement->getMontant())
                    ->getStyle('D'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(5, $row, $remboursement->getTresorerie()->getTitre());
            $sheet->setCellValueByColumnAndRow(6, $row, $remboursement->getReference());
            $sheet->setCellValueByColumnAndRow(7, $row, $remboursement->getRemarque());
        }
        $this->addBorder($sheet, $headers, $row);
        $this->autoSizeColumns($spreadsheet);

        $filename = 'Liste remboursements '. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Recupere détail de remboursement 
     */
    public function getDetailRemboursement(Remboursement $remboursement)
    {
        $exercice = $this->session->get('exercice');
        
        $spreadsheet = new Spreadsheet();
        // Feuille 1
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Détail remboursements '. $remboursement->getDate()->format('d_m_Y'));
  
        $prestations = $remboursement->getPrestations();

        $headers = ['N°Matricule', 'Nom et prénom', 'Date', 'Désignation', 'Frais', 'Remboursé', '% Remb'];
        $this->writeHeaders($sheet, $headers);
        
        // Writes values
        $row = 1;
        foreach ($prestations as $prestation) {
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $prestation->getPac()->getMatricule());
            $sheet->setCellValueByColumnAndRow(2, $row, $prestation->getPac()->getNomComplet());
            $sheet->setCellValueByColumnAndRow(3, $row, $prestation->getDate()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(4, $row, $prestation->getDesignation());
            $sheet->setCellValueByColumnAndRow(5, $row, $prestation->getFrais())
                    ->getStyle('E'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(6, $row, $prestation->getRembourse())
                    ->getStyle('F'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(7, $row, $prestation->getPercent().'%');
        }
        $this->addBorder($sheet, $headers, $row);
        $this->autoSizeColumns($spreadsheet);    

        $filename = 'Détail remboursement - '. $remboursement->getAdherent()->getNom() .' - '. $remboursement->getDate()->format('d_m_Y') .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /* 
     * EXPORTATION DES FICHIERS COMPTABLES
     */

    /*
     * Exportation journaux comptable
     */
    public function exportJournaux($in = null)
    {
        $codes = $this->manager->getRepository(Compte::class)->findCodeJournaux($in);

        $exercice = $this->session->get('exercice');
        $spreadsheet = new Spreadsheet();
        // Données
        $key = 0;
        foreach ($codes as $code) {       
            $articles = $this->manager->getRepository(Article::class)->findJournal($exercice, $code['codeJournal']);
            if ($articles) {
                if ($key != 0) {
                    $spreadsheet->createSheet();
                }  
                $spreadsheet->setActiveSheetIndex($key);
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($code['codeJournal']);

                $headers = ['Date', 'N° article', 'Libellé', 'Montant', 'Réference', 'Débit', 'Crédit', 'Déstination'];
                $this->writeHeaders($sheet, $headers);
                $row = 1;
                foreach ($articles as $article) {
                    $row++;
                    $sheet->setCellValueByColumnAndRow(1, $row, $article->getDate()->format('d/m/Y'));
                    $sheet->setCellValueByColumnAndRow(2, $row, $article->getId());
                    $sheet->setCellValueByColumnAndRow(3, $row, $article->getLibelle());
                    $sheet->setCellValueByColumnAndRow(4, $row, $article->getMontant())
                            ->getStyle('D'.$row)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->setCellValueByColumnAndRow(5, $row, $article->getPiece());
                    $sheet->setCellValueByColumnAndRow(6, $row, $article->getCompteDebit()->getPoste());
                    $sheet->setCellValueByColumnAndRow(7, $row, $article->getCompteCredit()->getPoste());
                    $sheet->setCellValueByColumnAndRow(8, $row, $article->getAnalytique());
                }
                $this->addBorder($sheet, $headers, $row);
                $key++;
            }
        }

        $this->autoSizeColumns($spreadsheet);    

        $filename = 'Journal '. $in.' '. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Exportation de grand livre comptable
     */
    public function exportGrandlivre()
    {
        $exercice = $this->session->get('exercice');
        $spreadsheet = new Spreadsheet();
        // Données
        $grandlivres = $this->manager->getRepository(Article::class)->findGrandLivre($exercice);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Grand livre des comptes '. $exercice->getAnnee());
  
        $headers = ['Date', 'Libellé', 'Référence', 'Journal', 'Débit', 'Crédit'];
        $this->writeHeaders($sheet, $headers);

        $row = 1;
        foreach ($grandlivres as $classe => $comptes) {
            $row++;
            $sheet->mergeCells('A'.$row.':F'.$row);
            $sheet->setCellValueByColumnAndRow(1, $row, $classe)
                    ->getStyle('A'.$row)->getFont()->setBold(true);
            foreach ($comptes as $compte => $articles) {
                $totalDebit = 0; $totalCredit = 0;
                $row++;
                $sheet->mergeCells('A'.$row.':F'.$row);
                $sheet->setCellValueByColumnAndRow(1, $row, $compte)
                        ->getStyle('A'.$row)->getFont()->setBold(true);
                foreach ($articles as $article) {
                    $row++;
                    $sheet->setCellValueByColumnAndRow(1, $row, $article['date']->format('d/m/Y'));
                    $sheet->setCellValueByColumnAndRow(2, $row, $article['libelle']);
                    $sheet->setCellValueByColumnAndRow(3, $row, $article['piece']);
                    $sheet->setCellValueByColumnAndRow(4, $row, $article['categorie']);
                    $sheet->setCellValueByColumnAndRow(5, $row, $article['debit'])
                            ->getStyle('E'.$row)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->setCellValueByColumnAndRow(6, $row, $article['credit'])
                            ->getStyle('F'.$row)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    $totalDebit += $article['debit'];
                    $totalCredit += $article['credit'];
                }
                $row++;
                $sheet->mergeCells('A'.$row.':D'.$row);
                $sheet->setCellValueByColumnAndRow(1, $row, 'Total '.$compte)
                            ->getStyle('A'.$row)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A'.$row)->getFont()->setBold(true);
                $sheet->setCellValueByColumnAndRow(5, $row, $totalDebit)
                            ->getStyle('E'.$row)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->setCellValueByColumnAndRow(6, $row, $totalCredit)
                            ->getStyle('F'.$row)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $row++;
                $sheet->mergeCells('A'.$row.':F'.$row);
            }

        }
        $this->autoSizeColumns($spreadsheet);
        $this->addBorder($sheet, $headers, $row);
        
        $filename = 'Grand livres des comptes - '. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Exportation de balance des comptes
     */
    public function exportBalance()
    {
        $exercice = $this->session->get('exercice');
        $spreadsheet = new Spreadsheet();
        // Données
        $balances = $this->manager->getRepository(Article::class)->findBalance($exercice);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Balance des comptes '. $exercice->getAnnee());
  
        $headers = ['Poste', 'Intitulé du compte', 'Mvt débit', 'Mvt crédit', 'Solde débit', 'Solde crédit'];
        $this->writeHeaders($sheet, $headers);

        $row = 1;
        foreach ($balances as $classe => $comptes) {
            $row++;
            $sheet->mergeCells('A'.$row.':F'.$row);
            $sheet->setCellValueByColumnAndRow(1, $row, $classe)
                    ->getStyle('A'.$row)->getFont()->setBold(true);

            $totalDebit = 0; $totalCredit = 0;
            foreach ($comptes as $compte) {   
                $row++;              
                $sheet->setCellValueByColumnAndRow(1, $row, $compte['poste'])
                        ->getStyle('A'.$row)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValueByColumnAndRow(2, $row, $compte['titre']);
                $sheet->setCellValueByColumnAndRow(3, $row, $compte['debit'])
                        ->getStyle('C'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->setCellValueByColumnAndRow(4, $row, $compte['credit'])
                        ->getStyle('D'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $solde_debiteur = ($compte['solde'] >= 0) ? $compte['solde'] : 0 ;
                $solde_crediteur = ($compte['solde'] >= 0) ? 0 : $compte['solde'];
                $sheet->setCellValueByColumnAndRow(5, $row, $solde_debiteur)
                        ->getStyle('E'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->setCellValueByColumnAndRow(6, $row, abs($solde_crediteur))
                        ->getStyle('F'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                
                $totalDebit += $compte['debit'];
                $totalCredit += $compte['credit'];
            }

            $row++;
            $sheet->mergeCells('A'.$row.':B'.$row);
            $sheet->setCellValueByColumnAndRow(1, $row, 'Total '.$classe)
                        ->getStyle('A'.$row)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValueByColumnAndRow(3, $row, $totalDebit)
                        ->getStyle('C'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(4, $row, $totalCredit)
                        ->getStyle('D'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $t_debiteur = (($totalDebit - $totalCredit) >= 0) ? ($totalDebit - $totalCredit) : 0 ;
            $t_crediteur = (($totalDebit - $totalCredit) >= 0) ? 0 : ($totalDebit - $totalCredit);
            $sheet->setCellValueByColumnAndRow(5, $row, $t_debiteur)
                        ->getStyle('E'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->setCellValueByColumnAndRow(6, $row, abs($t_crediteur))
                        ->getStyle('F'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        }
        $this->autoSizeColumns($spreadsheet);
        $this->addBorder($sheet, $headers, $row);
        
        $filename = 'Balance des comptes - '. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    /*
     * Exportation de l'etat fincancière
     */
    public function exportEtatFinanciere()
    {
        $exercice = $this->session->get('exercice');
        $spreadsheet = new Spreadsheet();
        
        // BILAN DES COMPTES 
        $bilanActif = $this->manager->getRepository(Compte::class)->findBilanActif($exercice);
        $bilanPassif = $this->manager->getRepository(Compte::class)->findBilanPassif($exercice);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bilan des actifs '. $exercice->getAnnee());
  
        // ACITF 
        $ret = $this->writeEtat($sheet, $bilanActif, 'actifs');
        $totalActifs = $ret['total']; // Total des actifs
        // PASSIF
        $row = $ret['lastRow'];
        $from = $row + 2;
        $row += 2; 
        $headers = ['Poste', 'Intitulé du compte', 'Solde'];
        $this->writeHeaders($sheet, $headers, $row);
        $total = 0;
        foreach ($bilanPassif as $compte) {
            $row++;         
            $sheet->setCellValueByColumnAndRow(1, $row, $compte['poste'])
                        ->getStyle('A'.$row)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT); 
            $sheet->setCellValueByColumnAndRow(2, $row, $compte['titre']);
            $sheet->setCellValueByColumnAndRow(3, $row, $compte['solde'])
                        ->getStyle('C'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $total += $compte['solde'];
        }

        // Ajouter le résultat de l'exercice
        $posteResultat = ($totalActifs > $total)?'120000':'129000';
        $titreResultat = ($totalActifs > $total)?'(Bénéfice)':'(Perte)';
        $row++;         
        $sheet->setCellValueByColumnAndRow(1, $row, $posteResultat)
                    ->getStyle('A'.$row)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT); 
        $sheet->setCellValueByColumnAndRow(2, $row, 'Resultat de l\'exercice'.$titreResultat);
        $sheet->setCellValueByColumnAndRow(3, $row, $totalActifs - $total)
                    ->getStyle('C'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // Total des passifs
        $row++;   
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->setCellValueByColumnAndRow(1, $row, 'Total des passifs et capitaux propres')
                    ->getStyle('A'.$row)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);      
        $sheet->setCellValueByColumnAndRow(3, $row, $totalActifs)
                    ->getStyle('C'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
        $this->addBorder($sheet, $headers, $row, $from);


        /* COMPTE DE RESULTAT */
        $charges = $this->manager->getRepository(Compte::class)->findGestionCharge($exercice);
        $produits = $this->manager->getRepository(Compte::class)->findGestionProduit($exercice);
        $sheet2 = new Worksheet($spreadsheet, 'Compte de résultat '. $exercice->getAnnee());
        $spreadsheet->addSheet($sheet2, 1);

        $retCharge = $this->writeEtat($sheet2, $charges, 'charges');
        $retProduit = $this->writeEtat($sheet2, $produits, 'produits', $retCharge['lastRow']+2);
        
        $row = $retProduit['lastRow']+2;   
        $sheet2->mergeCells('A'.$row.':B'.$row);
        $titreResultat = ($retProduit['total'] > $retCharge['total'])?'(Bénéfice)':'(Perte)';
        $sheet2->setCellValueByColumnAndRow(1, $row, 'Résultat de l\'exercice '.$titreResultat)
                    ->getStyle('A'.$row)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);      
        $sheet2->setCellValueByColumnAndRow(3, $row, $retProduit['total'] - $retCharge['total'])
                    ->getStyle('C'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet2->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);

        $this->autoSizeColumns($spreadsheet);
        $filename = 'Etats financières - '. $exercice->getAnnee() .'.xlsx';
     
        return $this->saveTemp($spreadsheet, $filename);
    }

    // Ce fonction est seulement utiliser pour eviter les copy
    private function saveSheetBeneficiare($sheet, $beneficiaires)
    {
        $headers = ['N° Matricule', 'Nom', 'Prenom', 'Sexe', 'Date de naissance', 'Tél', 'CIN', 'Date d\'entré', 'Fonction'];    
        $this->writeHeaders($sheet, $headers);  
        // Writes values
        $row = 1;
        foreach ($beneficiaires as $ancien) {
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $ancien->getCodeMutuelle());
            $sheet->setCellValueByColumnAndRow(2, $row, strtoupper($ancien->getNom()));
            $sheet->setCellValueByColumnAndRow(3, $row, $ancien->getPrenom());
            $sheet->setCellValueByColumnAndRow(4, $row, $ancien->getSexe());
            $sheet->setCellValueByColumnAndRow(5, $row, $ancien->getDateNaissance()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(6, $row, $ancien->getTel());
            $sheet->setCellValueByColumnAndRow(7, $row, $ancien->getCin());
            $sheet->setCellValueByColumnAndRow(8, $row, $ancien->getDateEntrer()->format('d/m/Y'));
            $sheet->setCellValueByColumnAndRow(9, $row, $ancien->getParente());
        }

        $this->addBorder($sheet, $headers, $row);
        return $sheet;
    }

    private function writeEtat($sheet, $comptes, $title, $row = 1)
    {
        $headers = ['Poste', 'Intitulé du compte', 'Solde'];
        $from = $row + 1; // Juste pour le border
        $this->writeHeaders($sheet, $headers, $row);
        $total = 0;
        foreach ($comptes as $compte) {
            $row++;         
            $sheet->setCellValueByColumnAndRow(1, $row, $compte['poste'])
                        ->getStyle('A'.$row)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT); 
            $sheet->setCellValueByColumnAndRow(2, $row, $compte['titre']);
            $sheet->setCellValueByColumnAndRow(3, $row, $compte['solde'])
                        ->getStyle('C'.$row)
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $total += $compte['solde'];
        }
        $row++;   
        $sheet->mergeCells('A'.$row.':B'.$row);
        $sheet->setCellValueByColumnAndRow(1, $row, 'Total des '.$title)
                    ->getStyle('A'.$row)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);      
        $sheet->setCellValueByColumnAndRow(3, $row, $total)
                    ->getStyle('C'.$row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle('A'.$row.':C'.$row)->getFont()->setBold(true);
        $this->addBorder($sheet, $headers, $row, $from);

        return ['total' => $total, 'lastRow' => $row];
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
        $sheet->getStyle(Coordinate::stringFromColumnIndex(1).$row.':'.Coordinate::stringFromColumnIndex(count($headers)).$row)
              ->applyFromArray($style)
              ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('066885');
    }

    private function addBorder($sheet, $headers, $row, $from =2)
    {
        $style = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '333333']
                ]
            ]
        ];

        $sheet->getStyle(Coordinate::stringFromColumnIndex(1).$from.':'.Coordinate::stringFromColumnIndex(count($headers)).$row)
                ->applyFromArray($style);
        return $sheet;
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