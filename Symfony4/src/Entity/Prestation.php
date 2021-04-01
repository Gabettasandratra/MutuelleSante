<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrestationRepository::class)
 */
class Prestation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $designation;

    /**
     * @ORM\Column(type="float")
     */
    private $frais;

    /**
     * @ORM\Column(type="float")
     */
    private $rembourse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prestataire;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facture;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPaye;

    /**
     * @ORM\ManyToOne(targetEntity=Pac::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pac;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="prestations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * @ORM\ManyToOne(targetEntity=Remboursement::class, inversedBy="prestations")
     */
    private $remboursement;

    /**
     * @ORM\Column(type="integer")
     */
    private $decompte;

    public function __construct(Pac $pac)
    {
        $this->date = new \DateTime(); // Afin que la date d'aujourdui sera afficher par defaut
        $this->pac = $pac;
        $this->adherent = $pac->getAdherent();
        $this->isPaye = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getFrais(): ?float
    {
        return $this->frais;
    }

    public function setFrais(float $frais): self
    {
        $this->frais = $frais;

        return $this;
    }

    public function getRembourse(): ?float
    {
        return $this->rembourse;
    }

    public function setRembourse(float $rembourse): self
    {
        $this->rembourse = $rembourse;

        return $this;
    }

    public function getPrestataire(): ?string
    {
        return $this->prestataire;
    }

    public function setPrestataire(?string $prestataire): self
    {
        $this->prestataire = $prestataire;

        return $this;
    }

    public function getFacture(): ?string
    {
        return $this->facture;
    }

    public function setFacture(?string $facture): self
    {
        $this->facture = $facture;

        return $this;
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

    public function getPac(): ?Pac
    {
        return $this->pac;
    }

    public function setPac(?Pac $pac): self
    {
        $this->pac = $pac;

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

    public function getRemboursement(): ?Remboursement
    {
        return $this->remboursement;
    }

    public function setRemboursement(?Remboursement $remboursement): self
    {
        $this->remboursement = $remboursement;

        return $this;
    }

    public function getDecompte(): ?int
    {
        return $this->decompte;
    }

    public function setDecompte(int $decompte): self
    {
        $this->decompte = $decompte;

        return $this;
    }
}