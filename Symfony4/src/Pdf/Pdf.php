<?php

namespace App\Pdf;

use Fpdf\Fpdf;

class Pdf extends Fpdf {


    public function __construct($periode,$title) {
        parent::__construct();
        $this->periode = $periode;
        $this->title = $title;
    }

    public function Header()
    {
        $this->RoundedRect(5, 5, 200, 30, 1);
        $this->SetFont('Arial','',10);
        
        $this->Cell(95,5,'MSRM',0,0,'L'); 
        $this->Cell(95,5,'Periode du '.$this->periode['debut']->format('d/m/Y'),0,1,'R'); // End of line

        $this->Cell(60,5,'CNPC Porte 14 Antanimena',0,0,'L');
        $this->SetFont('Arial','B',15);
        $this->Cell(70,5,$this->title,0,0,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(60,5,'Au '.$this->periode['fin']->format('d/m/Y'),0,1,'R'); // End

        $this->Cell(95,5,'034 10 776 69',0,0,'L');
        $this->Cell(95,5,'Tenue du compte : Ar ',0,1,'R'); // End

        $this->Cell(95,5,'smutuellesante@yahoo.fr',0,0,'L');
        $this->Cell(95,5,'Date de tirage : '.date('d/m/Y'),0,1,'R'); // End

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
        $this->Cell(8,8,'N°id', 'TBR',0, 'C');
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

        $this->Cell(17,8,'Date', 1,0, 'C');
        $this->Cell(10,8,'C.j', 'TBR',0, 'C');
        $this->Cell(8,8,'N°id', 'TBR',0, 'C');
        $this->Cell(70,8,'Libellé', 'TBR',0, 'C');
        $this->Cell(33,8,'Réference', 'TBR',0, 'C');
        $this->Cell(31,8,'Débit', 'TBR',0, 'C');
        $this->Cell(31,8,'Crédit', 'TBR',1, 'C');

        foreach ($livres as $classe => $comptes) {
            $this->SetX(5);
            $this->SetFont('Arial','BI',9);
            $this->Cell(200,6, strtoupper($classe), 'LBR',1, 'L');    
            
            $credit = 0; $debit = 0;
            foreach ($comptes as $compte => $articles) {
                $this->SetFont('Arial','I',9);
                $this->SetX(5);
                $this->Cell(200,6, $compte, 'LBR',1, 'L');

                $this->SetFont('Arial','',9);
                foreach ($articles as $article) {
                    $this->SetX(5);
                    $this->Cell(17,7, $article['date']->format('d/m/y'), 1,0, 'C');
                    $this->Cell(10,7, $article['categorie'], 'TBR',0, 'C');
                    $this->Cell(8,7, $article['id'], 'TBR',0, 'C');
                    $this->Cell(70,7, $article['libelle'], 'TBR',0, 'L');
                    $this->Cell(33,7, $article['piece'], 'TBR',0, 'L');
                    $this->Cell(31,7, $this->number_format($article['debit']), 'TBR',0, 'C'); $debit += $article['debit'];
                    $this->Cell(31,7, $this->number_format($article['credit']), 'TBR',1, 'C'); $credit += $article['credit'];
                }
                $this->SetX(5);
                $this->SetFont('Arial','BI',9);
                $this->Cell(138,6, 'Total '.$compte, 'LBR',0, 'R');              
                $this->Cell(31,6, $this->number_format($debit), 'BR',0, 'C'); 
                $this->Cell(31,6, $this->number_format($credit), 'BR',1, 'C');
            }
            
        }

    }

    public function number_format($float)
    {
        if ($float != 0) 
            return number_format($float,2,',',' ');
        return '';
    }
}