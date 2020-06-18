<?php

namespace App\Entity;

use App\Repository\CotisationEmiseRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Adherent;
use App\Entity\Exercice;

/**
 * @ORM\Entity(repositoryClass=CotisationEmiseRepository::class)
 */
class CotisationEmise
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $cotisations = [];

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="cotisationEmises")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * @ORM\ManyToOne(targetEntity=Exercice::class, inversedBy="cotisationEmises")
     * @ORM\JoinColumn(nullable=false)
     */
    private $exercice;

    public function __construct(Exercice $exercice, Adherent $adherent)
    {
        $this->exercice = $exercice;
        $this->adherent = $adherent;
        $montant1 = $adherent->getGarantie()->getMontant1();
        // Option 2 : Montant unique par béneficiaires
        foreach ($adherent->getTailleFamille() as $m => $n) {
            $this->cotisations[$m] = $n * $montant1;
        } 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCotisations(): ?array
    {
        return $this->cotisations;
    }

    public function setCotisations(array $cotisations): self
    {
        $this->cotisations = $cotisations;

        return $this;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): self
    {
        $this->adherent = $adherent;

        return $this;
    }

    public function getExercice(): ?Exercice
    {
        return $this->exercice;
    }

    public function setExercice(?Exercice $exercice): self
    {
        $this->exercice = $exercice;

        return $this;
    }
}
