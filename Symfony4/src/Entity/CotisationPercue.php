<?php

namespace App\Entity;

use App\Repository\CotisationPercueRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CotisationPercueRepository::class)
 */
class CotisationPercue
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
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="cotisationPercues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * @ORM\ManyToOne(targetEntity=Exercice::class, inversedBy="cotisationPercues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $exercice;

    public function __construct(Exercice $exercice, Adherent $adherent)
    {
        $this->exercice = $exercice;
        $this->adherent = $adherent;
        // Option 2 : Montant unique par béneficiaires
        foreach ($adherent->getTailleFamille() as $m => $n) {
            $this->cotisations[$m] = 0;
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
