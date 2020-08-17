<?php

namespace App\Pdf;

use Fpdf\Fpdf;

class Pdf extends Fpdf {
    public function Header()
    {
        // Police Arial italique 8
        $this->SetFont('Arial','B',13);
        // Numéro de page
        $this->Cell(0,10,'Mutuelle Santé',0,0,'C');
        $this->Ln();
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
}