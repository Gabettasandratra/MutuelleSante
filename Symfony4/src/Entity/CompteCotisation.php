<?php

namespace App\Entity;

use App\Entity\Adherent;
use App\Entity\Exercice;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\CompteCotisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CompteCotisationRepository::class)
 * @UniqueEntity(fields={"adherent", "exercice"}, message="Ce compte de cotisation est deja creer")
 */
class CompteCotisation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPaye;

    /**
     * @ORM\Column(type="float")
     */
    private $reste;

    /**
     * @ORM\Column(type="integer")
     */
    private $nouveau;

    /**
     * @ORM\Column(type="integer")
     */
    private $ancien;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="compteCotisations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * @ORM\ManyToOne(targetEntity=Exercice::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $exercice;

    /**
     * @ORM\OneToMany(targetEntity=HistoriqueCotisation::class, mappedBy="compteCotisation")
     */
    private $historiqueCotisations;

    /**
     * @ORM\Column(type="float")
     */
    private $paye;

    /**
     * @ORM\Column(type="float")
     */
    private $due;

    public function __construct(Exercice $exercice, Adherent $adherent)
    {   
        $this->isPaye = false;
        $this->due = 0;
        $this->paye = 0;
        $this->reste = 0;
        $this->nouveau = 0;
        $this->ancien = 0;
        $this->exercice = $exercice;
        $this->adherent = $adherent;
        $this->historiqueCotisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsPaye(): ?bool
    {
        return $this->isPaye;
    }

    public function setIsPaye(bool $isPaye): self
    {
        $this->isPaye = $isPaye;

        return $this;
    }

    public function getReste(): ?float
    {
        return $this->reste;
    }

    public function setReste(float $reste): self
    {
        $this->reste = $reste;

        return $this;
    }

    public function getNouveau(): ?int
    {
        return $this->nouveau;
    }

    public function setNouveau(int $nouveau): self
    {
        $this->nouveau = $nouveau;

        return $this;
    }

    public function getAncien(): ?int
    {
        return $this->ancien;
    }

    public function setAncien(int $ancien): self
    {
        $this->ancien = $ancien;

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

    // Logic on add new pac or retirer pac
    public function incrementNouveau() 
    {
        $this->nouveau = $this->nouveau + 1;
        $this->updateMontants();
    }

    public function incrementAncien() 
    {
        $this->ancien = $this->ancien + 1;
        $this->updateMontants();
    }

    public function decrementNouveau() 
    {
        $this->nouveau = $this->nouveau - 1;
        $this->updateMontants();
    }

    public function decrementAncien() 
    {
        $this->ancien = $this->ancien - 1;
        $this->updateMontants();
    }

    // mis à jour du due et reste à chaque ajout ou retrait de membre
    public function updateMontants()
    {
        $this->due = $this->nouveau * $this->exercice->getCotNouveau() + $this->exercice->getCotAncien() * $this->ancien;
        $this->reste = $this->due - $this->paye;
        if ($this->reste != 0) {
            $this->isPaye = false;
        }
    }

    // Get cotisation due
    public function payer($montant)
    {
        $this->paye = $this->paye + $montant;
        $this->updateMontants();
        if ($this->reste == 0) {
            $this->isPaye = true;
        }
    }
    
    /**
     * @return Collection|HistoriqueCotisation[]
     */
    public function getHistoriqueCotisations(): Collection
    {
        return $this->historiqueCotisations;
    }

    public function addHistoriqueCotisation(HistoriqueCotisation $historiqueCotisation): self
    {
        if (!$this->historiqueCotisations->contains($historiqueCotisation)) {
            $this->historiqueCotisations[] = $historiqueCotisation;
            $historiqueCotisation->setCompteCotisation($this);
        }

        return $this;
    }

    public function removeHistoriqueCotisation(HistoriqueCotisation $historiqueCotisation): self
    {
        if ($this->historiqueCotisations->contains($historiqueCotisation)) {
            $this->historiqueCotisations->removeElement($historiqueCotisation);
            // set the owning side to null (unless already changed)
            if ($historiqueCotisation->getCompteCotisation() === $this) {
                $historiqueCotisation->setCompteCotisation(null);
            }
        }

        return $this;
    }

    public function getPaye(): ?float
    {
        return $this->paye;
    }

    public function setPaye(float $paye): self
    {
        $this->paye = $paye;

        return $this;
    }

    public function getDue(): ?float
    {
        return $this->due;
    }

    public function setDue(float $due): self
    {
        $this->due = $due;

        return $this;
    }

}
