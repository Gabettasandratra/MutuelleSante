<?php

namespace App\Entity;

use App\Repository\ArriereAvanceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArriereAvanceRepository::class)
 */
class ArriereAvance
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
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="arriereAvances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * @ORM\ManyToOne(targetEntity=Exercice::class, inversedBy="arriereAvances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $exercice;

    public function __construct(Exercice $exercice, Adherent $adherent)
    {
        $this->exercice = $exercice;
        $cotisationsPercue = $adherent->getCurrentCotisationPercue()->getCotisations();
        $cotisationsEmise = $adherent->getCurrentCotisationEmise()->getCotisations();
        $tailleFamille = $adherent->getTailleFamille();
        // Arriere avance = cotisation percue - cotisation emise
        foreach ($tailleFamille as $m => $n) {
            $this->cotisations[$m] = $cotisationsPercue[$m] - $cotisationsEmise[$m];
            // Cummul arriere
            if($m !== array_key_first($tailleFamille)) {
                $this->cotisations[$m] += $this->cotisations[$m - 1];
            }
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
