<?php

namespace App\Entity;

use App\Repository\GarantieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GarantieRepository::class)
 */
class Garantie
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
    private $nom;

    /**
     * @ORM\Column(type="float")
     */
    private $droitAdhesion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $delaiRetard;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $delaiReprise;

    /**
     * @ORM\Column(type="integer")
     */
    private $periodeObservation;

    /**
     * @ORM\Column(type="float")
     */
    private $montant1;

    /**
     * @ORM\ManyToOne(targetEntity=TypeCotisation::class, inversedBy="garanties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeCotisation;

    /**
     * @ORM\OneToMany(targetEntity=Adherent::class, mappedBy="garantie")
     */
    private $adherents;

    public function __construct()
    {
        $this->adherents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDelaiRetard(): ?int
    {
        return $this->delaiRetard;
    }

    public function setDelaiRetard(?int $delaiRetard): self
    {
        $this->delaiRetard = $delaiRetard;

        return $this;
    }

    public function getDelaiReprise(): ?int
    {
        return $this->delaiReprise;
    }

    public function setDelaiReprise(?int $delaiReprise): self
    {
        $this->delaiReprise = $delaiReprise;

        return $this;
    }

    public function getPeriodeObservation(): ?int
    {
        return $this->periodeObservation;
    }

    public function setPeriodeObservation(int $periodeObservation): self
    {
        $this->periodeObservation = $periodeObservation;

        return $this;
    }

    public function getMontant1(): ?float
    {
        return $this->montant1;
    }

    public function setMontant1(float $montant1): self
    {
        $this->montant1 = $montant1;

        return $this;
    }

    public function getTypeCotisation(): ?TypeCotisation
    {
        return $this->typeCotisation;
    }

    public function setTypeCotisation(?TypeCotisation $typeCotisation): self
    {
        $this->typeCotisation = $typeCotisation;

        return $this;
    }

    /**
     * @return Collection|Adherent[]
     */
    public function getAdherents(): Collection
    {
        return $this->adherents;
    }

    public function addAdherent(Adherent $adherent): self
    {
        if (!$this->adherents->contains($adherent)) {
            $this->adherents[] = $adherent;
            $adherent->setGarantie($this);
        }

        return $this;
    }

    public function removeAdherent(Adherent $adherent): self
    {
        if ($this->adherents->contains($adherent)) {
            $this->adherents->removeElement($adherent);
            // set the owning side to null (unless already changed)
            if ($adherent->getGarantie() === $this) {
                $adherent->setGarantie(null);
            }
        }

        return $this;
    }
}
