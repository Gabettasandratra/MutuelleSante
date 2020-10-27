<?php

namespace App\Pdf;

use Fpdf\Fpdf;

class Pdf extends Fpdf {
    public function __construct($periode, $mutuelle,$title,$subtitle=null) {
        parent::__construct();
        $this->periode = $periode;
        $this->mutuelle = $mutuelle;
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    public function Header()
    {
        $this->RoundedRect(5, 5, 200, 30, 1);
        $this->SetFont('Arial','',10);
        
        $this->Cell(95,5, $this->mutuelle['nom_mutuelle'],0,0,'L'); 
        $this->Cell(95,5,'Periode du '.$this->periode['debut']->format('d/m/Y'),0,1,'R'); // End of line

        $this->Cell(60,5,$this->mutuelle['adresse_mutuelle'],0,0,'L');
        $this->SetFont('Arial','B',15);
        $this->Cell(70,8,$this->title,0,0,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(60,5,'Au '.$this->periode['fin']->format('d/m/Y'),0,1,'R'); // End

        $this->Cell(95,5,$this->mutuelle['contact_mutuelle'],0,0,'L');
        $this->Cell(95,5,'Tenue du compte : Ar ',0,1,'R'); // End

        $this->Cell(60,5,$this->mutuelle['email_mutuelle'],0,0,'L');
        $this->Cell(70,5, $this->subtitle,0,0,'C');
        $this->Cell(60,5,'Date de tirage : '.date('d/m/Y'),0,1,'R'); // End

        // Numéro de page
        //make a dummy empty cell as a vertical spacer
        $this->Cell(0,10,'',0,1);//end of line
    }

    public function Footer()
    {
        // Positionnement à 1,5 cm du bas
        
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial','I',8);
        // Numéro de page
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        if (strpos($corners, '2')===false)
            $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
        else
            $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($corners, '3')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($corners, '4')===false)
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        if (strpos($corners, '1')===false)
        {
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
            $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
        }
        else
            $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    public function balanceTable($balances)
    {
        // Header
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(16,12,'Poste', 1,0, 'C');
        $this->Cell(60,12,'Intitulé des comptes', 1,0, 'C');
        $x = $this->GetX();
        $this->Cell(62,6,'Mouvements', 1,0, 'C');
        $this->Cell(62,6,'Soldes', 1,1, 'C');
        
        $this->SetX($x);
        $this->Cell(31,6,'Débit', 1,0, 'C');
        $this->Cell(31,6,'Crédit', 1,0, 'C');
        $this->Cell(31,6,'Débiteur', 1,0, 'C');
        $this->Cell(31,6,'Créditeur', 1,1, 'C');
        
        // Les donnees
        $Tcredit = 0; $Tdebit = 0; $TsoldeD = 0; $TsoldeC = 0;
        // - A chaque classe
        foreach ($balances as $classe => $comptes) {
            $this->SetX(5);
            $this->SetFont('Arial','I',9);
            $this->Cell(200,6, strtoupper($classe), 'LBR',1, 'L');
            $this->SetFont('Arial','',9);

            $credit = 0; $debit = 0; $soldeD = 0; $soldeC = 0;
            foreach ($comptes as $compte) {
                $this->SetX(5);
                $this->Cell(16,6, $compte['poste'], 'LB',0, 'C');
                $this->Cell(60,6, $compte['titre'], 'BR',0, 'L');
                $this->Cell(31,6, $this->number_format($compte['debit']), 'BR',0, 'C'); $debit +=$compte['debit'];
                $this->Cell(31,6, $this->number_format($compte['credit']), 'BR',0, 'C'); $credit +=$compte['credit'];
                $this->Cell(31,6, $this->number_format($compte['soldeD']), 'BR',0, 'C'); $soldeD +=$compte['soldeD'];
                $this->Cell(31,6, $this->number_format($compte['soldeC']), 'BR',1, 'C'); $soldeC +=$compte['soldeC'];
            }

            $this->SetX(5);
            $this->SetFont('Arial','I',9);
            $this->Cell(76,6, 'Total '.strtoupper($classe), 'LBR',0, 'R');
            $this->Cell(31,6, $this->number_format($debit), 'BR',0, 'C'); $Tdebit +=$debit;
            $this->Cell(31,6, $this->number_format($credit), 'BR',0, 'C'); $Tcredit +=$credit;
            $this->Cell(31,6, $this->number_format($soldeD), 'BR',0, 'C'); $TsoldeD +=$soldeD;
            $this->Cell(31,6, $this->number_format($soldeC), 'BR',1, 'C'); $TsoldeC +=$soldeC;
        }
        // Totaux de la balance
        $this->SetX(5);
        $this->SetFont('Arial','I',9);
        $this->Cell(76,6, 'Totaux de la balance', 'LBR',0, 'R');
        $this->Cell(31,6, $this->number_format($Tdebit), 'BR',0, 'C'); 
        $this->Cell(31,6, $this->number_format($Tcredit), 'BR',0, 'C');
        $this->Cell(31,6, $this->number_format($TsoldeD), 'BR',0, 'C'); 
        $this->Cell(31,6, $this->number_format($TsoldeC), 'BR',1, 'C');
    }

    public function journalTable($articles)
    {
        // Header
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(17,8,'Date', 1,0, 'C');
        $this->Cell(8,8,'N°', 'TBR',0, 'C');
        $this->Cell(16,8,'Débité', 'TBR',0, 'C');
        $this->Cell(16,8,'Crédité', 'TBR',0, 'C');
        $this->Cell(63,8,'Libellé écriture', 'TBR',0, 'C');
        $this->Cell(31,8,'Montant', 'TBR',0, 'C');
        $this->Cell(39,8,'Réference', 'TBR',0, 'C');
        $this->Cell(10,8,'Dest', 'TBR',1, 'C');
        
        // Donnees
        $this->SetFont('Arial','',9);
        foreach ($articles as $article) {
            $this->SetX(5);
            $this->Cell(17,6, $article->getDate()->format('d/m/y'), 'LR',0, 'C');
            $this->Cell(8,6,$article->getId(), 'R',0, 'C');
            $this->Cell(16,6,$article->getCompteDebit()->getPoste(), 'R',0, 'C');
            $this->Cell(16,6,$article->getCompteCredit()->getPoste(), 'R',0, 'C');
            $this->Cell(63,6,$article->getLibelle(), 'R',0, 'L');
            $this->Cell(31,6,$this->number_format($article->getMontant()), 'R',0, 'C');
            $this->Cell(39,6,$article->getPiece(), 'R',0, 'L');
            $this->Cell(10,6,$article->getAnalytique(), 'R',1, 'C');
        }
        $this->SetX(5);
        $this->Cell(200,1,'','T',1);
    }

    public function livreTable($livres)
    {
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(17,8,'Date', 'TLR',0, 'C');
        $this->Cell(10,8,'C.j', 'TR',0, 'C');
        $this->Cell(8,8,'N°', 'TR',0, 'C');
        $this->Cell(103,8,'Libellé écriture', 'TR',0, 'C');
        $this->Cell(31,8,'Débit', 'TR',0, 'C');
        $this->Cell(31,8,'Crédit', 'TR',1, 'C');

        foreach ($livres as $classe => $comptes) {
            $this->SetX(5);
            $this->SetFont('Arial','BI',9);
            $this->Cell(200,6, ' '.strtoupper($classe), 'TLR',1, 'L');    
            
            $credit = 0; $debit = 0;
            foreach ($comptes as $compte => $articles) {
                $this->SetFont('Arial','I',9);
                $this->SetX(5);
                $this->Cell(200,6,  ' '.$compte, 'TLR',1, 'L');

                $this->SetFont('Arial','',9);
                foreach ($articles as $article) {
                    $this->SetX(5);
                    $this->Cell(17,6, $article['date']->format('d/m/y'), 'TLR',0, 'C');
                    $this->Cell(10,6, $article['categorie'], 'TR',0, 'C');
                    $this->Cell(8,6, $article['id'], 'TR',0, 'C');
                    $this->Cell(103,6, $article['libelle'], 'TR',0, 'L');
                    $this->Cell(31,6, $this->number_format($article['debit']), 'TR',0, 'C'); $debit += $article['debit'];
                    $this->Cell(31,6, $this->number_format($article['credit']), 'TR',1, 'C'); $credit += $article['credit'];
                }
                $this->SetX(5);
                $this->SetFont('Arial','BI',9);
                $this->Cell(138,6, 'Total '.$compte, 1,0, 'R');              
                $this->Cell(31,6, $this->number_format($debit), 'TBR',0, 'C'); 
                $this->Cell(31,6, $this->number_format($credit), 'TBR',1, 'C');
            }
            
        }

    }

    public function bilanActifTable($bilan)
    {
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(101,8,'Détail des postes', 1,0, 'C');
        $this->Cell(33,8,'Brut', 'TBR',0, 'C');
        $this->Cell(33,8,'Amorts & Prov', 'TBR',0, 'C');
        $this->Cell(33,8,'Net', 'TBR',1, 'C');

        // Data
        $anc = $this->printBilan('ACTIF NON COURANT', $bilan['anc']); // Actif non courant
        $ac = $this->printBilan('ACTIF COURANT', $bilan['ac']); // Actif non courant
        // Total des actifs
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(101,6, 'TOTAL', 'LBR',0, 'R');  
        $this->Cell(33,6, $this->number_format($anc['tB']+$ac['tB']), 'BR',0, 'C');
        $this->Cell(33,6, $this->number_format(-1*($anc['tAmort']+$ac['tAmort'])), 'BR',0, 'C');
        $this->Cell(33,6, $this->number_format($anc['tB']+$ac['tB']+$anc['tAmort']+$ac['tAmort']), 'BR',1, 'C');

        // Change the title
        $this->title = "Bilan passif";
    }

    public function bilanPassifTable($bilan)
    {
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(160,8,'Détail des postes', 1,0, 'C');
        $this->Cell(40,8,'Montant', 'TBR',1, 'C');
        
        // Data
        $t = 0;
        $t += $this->printBilanPassif('CAPITAUX PROPRES', $bilan['cp']); // Capitaux propres
        $t += $this->printBilanPassif('PASSIF NON COURANT', $bilan['pnc']); // Actif non courant
        $t += $this->printBilanPassif('PASSIF COURANT', $bilan['pc']); // Actif non courant

        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(160,6, 'TOTAL', 'LBR',0, 'R');  
        $this->Cell(40,6, $this->number_format(-1*$t), 'BR',1, 'C');
    }

    public function printBilan($rubrique, $donnees)
    {
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(200,6, strtoupper($rubrique), 'LBR',1, 'L');  
        $tB = 0; $tAmort = 0;
        foreach ($donnees as $poste) {    
            if ($poste[0] == 0) {
                $this->SetX(5);
                $this->SetFont('Arial','BI',9);
                $this->Cell(101,6, $poste[1], 'LBR',0, 'L');
                
            } else {
                $this->SetX(5);
                $this->SetFont('Arial','',9);
                $this->Cell(101,6, '  '.$poste[1], 'LBR',0, 'L');
            }

            $this->SetFont('Arial','',9);
            if (count($poste) == 2) {
                $this->Cell(33,6, '', 'BR',0, 'C');
                $this->Cell(33,6, '', 'BR',0, 'C');
                $this->Cell(33,6, '', 'BR',1, 'C');
            } else {
                $this->Cell(33,6, $this->number_format($poste[2]), 'BR',0, 'C'); $tB += $poste[2];
                $this->Cell(33,6, $this->number_format(-1*$poste[3]), 'BR',0, 'C'); $tAmort += $poste[3];
                $this->Cell(33,6, $this->number_format($poste[2]+$poste[3]), 'BR',1, 'C');
            }
        }
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(101,6, 'TOTAL '.strtoupper($rubrique), 'LBR',0, 'R');  
        $this->Cell(33,6, $this->number_format($tB), 'BR',0, 'C');
        $this->Cell(33,6, $this->number_format(-1*$tAmort), 'BR',0, 'C');
        $this->Cell(33,6, $this->number_format($tB+$tAmort), 'BR',1, 'C');

        return ['tB' => $tB, 'tAmort' => $tAmort];
    }

    public function printBilanPassif($rubrique, $donnees)
    {
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(200,6, strtoupper($rubrique), 'LBR',1, 'L');  
        $tB = 0; 
        foreach ($donnees as $poste) {    
            if ($poste[0] == 0) {
                $this->SetX(5);
                $this->SetFont('Arial','',9);
                $this->Cell(160,6, $poste[1], 'LBR',0, 'L');
                
            } else {
                $this->SetX(5);
                $this->SetFont('Arial','I',9);
                $this->Cell(160,6, '  '.$poste[1], 'LBR',0, 'L');
            }

            $this->SetFont('Arial','',9);
            if (count($poste) == 2)
                $this->Cell(40,6, '', 'BR',1, 'C');
            else        
                $this->Cell(40,6, $this->number_format(-1*$poste[2]), 'BR',1, 'C'); $tB += $poste[2];
        }

        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(160,6, 'TOTAL '.strtoupper($rubrique), 'LBR',0, 'R');  
        $this->Cell(40,6, $this->number_format(-1*$tB), 'BR',1, 'C');

        return $tB;
    }

    public function compteResultatTable($donnees)
    {       
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(160,8,'Détail des postes', 1,0, 'C');
        $this->Cell(40,8,'Montant', 'TBR',1, 'C');

        // charge financiers
        $rE = 0; $op = 0; $rF = 0; $re = 0; $im = 0;
        // Produit exploitation
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(200,6, 'PRODUIT D\'EXPLOITATION', 'LBR',1, 'L'); 
        $rE += $this->printDonneesResultat($donnees['ca']);
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(160,6, "Chiffre d'affaire net" , 'LBR',0, 'R'); 
        $this->Cell(40,6, $this->number_format(-1*$rE), 'BR',1, 'C');
        $rE += $this->printDonneesResultat($donnees['pE']);
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(160,6, "TOTAL PRODUIT D'EXPLOITATION (I)" , 'LBR',0, 'R'); 
        $this->Cell(40,6, $this->number_format(-1*$rE), 'BR',1, 'C');

        $rE += $this->printResultat('CHARGE D\'EXPLOITATION', $donnees['cE'],'II', false);
        $this->printString('1 - RESULTAT D\'EXPLOITATION (I-II)', $rE);

        $op += $this->printResultat('OPERATION EN COMMUN', $donnees['op'], 'III-IV'); 
        $rF += $this->printResultat('PRODUIT FINANCIER', $donnees['pf'],'V'); 
        $rF += $this->printResultat('CHARGE FINANCIER', $donnees['cf'],'VI', false);
        $this->printString('2 - RESULTAT FINANCIER (V-VI)', $rF);

        $this->printString('3 - RESULTAT COURANT AVANT IMPOT (I-II+III-IV+V-VI)', $rE+$rF+$op);
        
        $re += $this->printResultat('PRODUIT EXCEPTIONNEL', $donnees['pe'],'VII'); 
        $re += $this->printResultat('CHARGE EXCEPTIONNEL', $donnees['ce'],'VIII', false); 
        $this->printString('4 - RESULTAT EXCEPTIONNEL (VII-VIII)', $re);
        $im += $this->printResultat('IMPOT', $donnees['im'],'IX+X', false); 

        // Resutat final 
        $r = $rE+$rF+$op+$re+$im;
        $ben = ($r < 0)?"(BENEFICE)":"(PERTE)";
        $this->printString("5 - RESULTAT DE L'EXERCICE ".$ben, $rE+$rF+$op+$re+$im);

    }

    public function printString($poste, $montant)
    {
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(160,8, strtoupper($poste), 'LBR',0, 'C'); 
        $this->Cell(40,8, $this->number_format(-1*$montant), 'BR',1, 'C');
    }

    public function printResultat($rubrique, $donnees, $num = '', $produit = true)
    {
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(200,6, strtoupper($rubrique), 'LBR',1, 'L');  
        $tB = 0; 
        $c = ($produit)?-1:1; // 1 ou -1
        foreach ($donnees as $poste) {    
            if ($poste[0] == 0) {
                $this->SetX(5);
                $this->SetFont('Arial','',9);
                $this->Cell(160,6, $poste[1], 'LBR',0, 'L');
                
            } else {
                $this->SetX(5);
                $this->SetFont('Arial','I',9);
                $this->Cell(160,6, '  '.$poste[1], 'LBR',0, 'L');
            }

            $this->SetFont('Arial','',9);
            
            if (count($poste) == 2)
                $this->Cell(40,6, '', 'BR',1, 'C');
            else {
                $tB += $poste[2];
                if ($rubrique == 'OPERATION EN COMMUN') 
                    $poste[2] = -1*abs($poste[2]);
                $this->Cell(40,6, $this->number_format($c*$poste[2]), 'BR',1, 'C'); 
            }
        }
        $this->SetX(5);
        $this->SetFont('Arial','BI',9);
        $this->Cell(160,6, 'TOTAL '.strtoupper($rubrique).' ('.$num.')' , 'LBR',0, 'R'); 
        $this->Cell(40,6, $this->number_format($c*$tB), 'BR',1, 'C');

        return $tB;
    }

    public function printDonneesResultat($donnees, $produit = true)
    {
        $tB = 0; 
        $c = ($produit)?-1:1; // 1 ou -1
        foreach ($donnees as $poste) {    
            if ($poste[0] == 0) {
                $this->SetX(5);
                $this->SetFont('Arial','',9);
                $this->Cell(160,6, $poste[1], 'LBR',0, 'L');              
            } else {
                $this->SetX(5);
                $this->SetFont('Arial','I',9);
                $this->Cell(160,6, '  '.$poste[1], 'LBR',0, 'L');
            }
            $this->SetFont('Arial','',9);          
            if (count($poste) == 2)
                $this->Cell(40,6, '', 'BR',1, 'C');
            else {
                $this->Cell(40,6, $this->number_format($c*$poste[2]), 'BR',1, 'C'); $tB += $poste[2];
            }
        }
        return $tB;
    }
    public function number_format($float)
    {
        if ($float != 0) 
            return number_format($float,2,',',' ');
        return '';
    }
}