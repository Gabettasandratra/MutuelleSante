<?php

namespace App\Entity;

use App\Repository\ExerciceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ExerciceRepository::class)
 * @UniqueEntity(fields={"annee"}, message="L'exercice {{ value }} est déja configuré")
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
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $cotNouveau;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $cotAncien;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateFin;

    /**
     * @ORM\OneToMany(targetEntity=Budget::class, mappedBy="exercice", orphanRemoval=true)
     */
    private $budgets;

    public function __construct()
    {
        $this->isCloture = false;   
        $this->historiqueCotisations = new ArrayCollection();
        $this->budgets = new ArrayCollection();
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

    public function getIsCurrent()
    {       
        $now = new \DateTime();
        if ( $this->dateDebut <= $now && $this->dateFin >= $now) {
            return true;
        }
        return false;
    }

    public function check(\DateTimeInterface $date)
    {       
        if ( $this->dateDebut <= $date && $this->dateFin >= $date) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection|Budget[]
     */
    public function getBudgets(): Collection
    {
        return $this->budgets;
    }

    public function addBudgets(Budget $budgets): self
    {
        if (!$this->budgets->contains($budgets)) {
            $this->budgets[] = $budgets;
            $budgets->setExercice($this);
        }

        return $this;
    }

    public function removeBudgets(Budget $budgets): self
    {
        if ($this->budgets->contains($budgets)) {
            $this->budgets->removeElement($budgets);
            // set the owning side to null (unless already changed)
            if ($budgets->getExercice() === $this) {
                $budgets->setExercice(null);
            }
        }

        return $this;
    }
}
