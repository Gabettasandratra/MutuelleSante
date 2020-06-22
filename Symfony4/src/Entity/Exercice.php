<?php

namespace App\Entity;

use App\Repository\ExerciceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExerciceRepository::class)
 */
class Exercice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $annee;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCloture;

    /**
     * @ORM\OneToMany(targetEntity=HistoriqueCotisation::class, mappedBy="exercice", orphanRemoval=true)
     */
    private $historiqueCotisations;

    /**
     * @ORM\Column(type="float")
     */
    private $cotNouveau;

    /**
     * @ORM\Column(type="float")
     */
    private $cotAncien;

    /**
     * @ORM\Column(type="float")
     */
    private $droitAdhesion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateFin;

    public function __construct()
    {
        $this->historiqueCotisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getIsCloture(): ?bool
    {
        return $this->isCloture;
    }

    public function setIsCloture(bool $isCloture): self
    {
        $this->isCloture = $isCloture;

        return $this;
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
            $historiqueCotisation->setExercice($this);
        }

        return $this;
    }

    public function removeHistoriqueCotisation(HistoriqueCotisation $historiqueCotisation): self
    {
        if ($this->historiqueCotisations->contains($historiqueCotisation)) {
            $this->historiqueCotisations->removeElement($historiqueCotisation);
            // set the owning side to null (unless already changed)
            if ($historiqueCotisation->getExercice() === $this) {
                $historiqueCotisation->setExercice(null);
            }
        }

        return $this;
    }

    public function getCotNouveau(): ?float
    {
        return $this->cotNouveau;
    }

    public function setCotNouveau(float $cotNouveau): self
    {
        $this->cotNouveau = $cotNouveau;

        return $this;
    }

    public function getCotAncien(): ?float
    {
        return $this->cotAncien;
    }

    public function setCotAncien(float $cotAncien): self
    {
        $this->cotAncien = $cotAncien;

        return $this;
    }

    public function getDroitAdhesion(): ?float
    {
        return $this->droitAdhesion;
    }

    public function setDroitAdhesion(float $droitAdhesion): self
    {
        $this->droitAdhesion = $droitAdhesion;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }
}
