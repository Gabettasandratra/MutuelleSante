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
     * @ORM\OneToMany(targetEntity=CotisationEmise::class, mappedBy="exercice", orphanRemoval=true)
     */
    private $cotisationEmises;

    /**
     * @ORM\OneToMany(targetEntity=CotisationPercue::class, mappedBy="exercice", orphanRemoval=true)
     */
    private $cotisationPercues;

    public function __construct()
    {
        $this->cotisationEmises = new ArrayCollection();
        $this->cotisationPercues = new ArrayCollection();
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
     * @return Collection|CotisationEmise[]
     */
    public function getCotisationEmises(): Collection
    {
        return $this->cotisationEmises;
    }

    public function addCotisationEmise(CotisationEmise $cotisationEmise): self
    {
        if (!$this->cotisationEmises->contains($cotisationEmise)) {
            $this->cotisationEmises[] = $cotisationEmise;
            $cotisationEmise->setExercice($this);
        }

        return $this;
    }

    public function removeCotisationEmise(CotisationEmise $cotisationEmise): self
    {
        if ($this->cotisationEmises->contains($cotisationEmise)) {
            $this->cotisationEmises->removeElement($cotisationEmise);
            // set the owning side to null (unless already changed)
            if ($cotisationEmise->getExercice() === $this) {
                $cotisationEmise->setExercice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CotisationPercue[]
     */
    public function getCotisationPercues(): Collection
    {
        return $this->cotisationPercues;
    }

    public function addCotisationPercue(CotisationPercue $cotisationPercue): self
    {
        if (!$this->cotisationPercues->contains($cotisationPercue)) {
            $this->cotisationPercues[] = $cotisationPercue;
            $cotisationPercue->setExercice($this);
        }

        return $this;
    }

    public function removeCotisationPercue(CotisationPercue $cotisationPercue): self
    {
        if ($this->cotisationPercues->contains($cotisationPercue)) {
            $this->cotisationPercues->removeElement($cotisationPercue);
            // set the owning side to null (unless already changed)
            if ($cotisationPercue->getExercice() === $this) {
                $cotisationPercue->setExercice(null);
            }
        }

        return $this;
    }
}
