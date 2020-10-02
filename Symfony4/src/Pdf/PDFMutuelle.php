<?php

namespace App\Pdf;

use Fpdf\Fpdf;

class PDFMutuelle extends Fpdf {

    public function __construct($periode,$title,$subtitle=null) {
        parent::__construct();
        $this->periode = $periode;
        $this->title = $title;
        $this->subtitle = $subtitle;
    }

    /*
    public function Header()
    {
        $this->RoundedRect(5, 5, 200, 30, 1);
        $this->SetFont('Arial','',10);
        
        $this->Cell(95,5,'MSRM',0,0,'L'); 
        $this->Cell(95,5,'',0,1,'R'); // End of line

        $this->Cell(60,5,'CNPC Porte 14 Antanimena',0,0,'L');
        $this->SetFont('Arial','B',15);
        $this->Cell(70,8,$this->title,0,0,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(60,5,'',0,1,'R'); // End

        $this->Cell(95,5,'034 10 776 69',0,0,'L');
        $this->Cell(95,5,'',0,1,'R'); // End

        $this->Cell(60,5,'smutuellesante@yahoo.fr',0,0,'L');
        $this->Cell(70,5, $this->subtitle,0,0,'C');
        $this->Cell(60,5,'Date de tirage : '.date('d/m/Y'),0,1,'R'); // End
        $this->Cell(0,10,'',0,1);//end of line
    }*/

    public function Footer()
    {
        // Positionnement à 1,5 cm du ba       
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

    public function adhesionTable($congs)
    {
        // Header
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(10,8,'N°', 1,0, 'C');
        $this->Cell(75,8,'Nom', 'TBR',0, 'C');
        $this->Cell(25,8,'Date d\'adhé', 'TBR',0, 'C');
        $this->Cell(17,8,'Nb bén', 'TBR',0, 'C');
        $this->Cell(60,8,'Adresse', 'TBR',0, 'C');
        $this->Cell(40,8,'Téléphone', 'TBR',0, 'C');
        $this->Cell(60,8,'Email', 'TBR',1, 'C');
        
        // Donnees
        $this->SetFont('Arial','',9);
        foreach ($congs as $cong) {
            $this->SetX(5);
            $this->Cell(10,7, $cong->getNumero(), 'LR',0, 'C');
            $this->Cell(75,7, $cong->getNom(), 'LR',0, 'L');
            $this->Cell(25,7, $cong->getDateInscription()->format('d/m/Y'), 'LR',0, 'C');
            $this->Cell(17,7, $cong->getNbBeneficiaires(), 'LR',0, 'C');
            $this->Cell(60,7, $cong->getAdresse(), 'LR',0, 'L');
            $this->Cell(40,7, $cong->getTelephone(), 'LR',0, 'L');
            $this->Cell(60,7, $cong->getEmail(), 'LR',1, 'L');
        }

        $this->SetX(5);
        $this->Cell(287,1,'','T',1);
    }

    public function beneficiaireTable($beneficiaires)
    {
        // Header
        $this->SetX(5);
        $this->SetFont('Arial','B',10);

        $this->Cell(17,8,'N°Matri', 1,0, 'C');
        $this->Cell(70,8,'Nom & prénom', 'TBR',0, 'C');
        $this->Cell(8,8,'G', 'TBR',0, 'C');
        $this->Cell(20,8,'Date Nais', 'TBR',0, 'C');
        $this->Cell(30,8,'Tél', 'TBR',0, 'C');
        $this->Cell(35,8,'CIN', 'TBR',0, 'C');
        $this->Cell(20,8,'Date entré', 'TBR',1, 'C');
        
        // Donnees
        $this->SetFont('Arial','',9);
        foreach ($beneficiaires as $ben) {
            $this->SetX(5);
            $this->Cell(17,7, $ben->getMatricule(), 'LR',0, 'C');
            $this->Cell(70,7, $ben->getNomComplet(), 'R',0, 'L');
            $this->Cell(8,7, $ben->getSexe()[0], 'R',0, 'C');
            $this->Cell(20,7, $ben->getDateNaissance()->format('d/m/Y'), 'R',0, 'C');
            $this->Cell(30,7, $ben->getTel(), 'R',0, 'L');
            $this->Cell(35,7, $ben->getCin(), 'R',0, 'L');
            $this->Cell(20,7, $ben->getDateEntrer()->format('d/m/Y'), 'R',1, 'C');
        }

        $this->SetX(5);
        $this->Cell(200,1,'','T',1);
    }
}
