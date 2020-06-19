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
}
