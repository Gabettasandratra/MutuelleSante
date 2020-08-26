<?php

namespace App\Entity;

use App\Repository\PrestationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\LessThan("+1 day", message="La date est supérieur à la date d'aujourdhui!")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $designation;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $frais;

    /**
     * @ORM\Column(type="float")
     * @Assert\PositiveOrZero
     * @Assert\LessThan(propertyPath="frais", message="Le montant remboursé ne doit pas depasser le frais")
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

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDecision;

    public function __construct(Pac $pac)
    {
        $this->date = new \DateTime(); // Afin que la date d'aujourdui sera afficher par defaut
        $this->pac = $pac;
        $this->adherent = $pac->getAdherent();
        $this->isPaye = false;
        $this->dateDecision = new \DateTime(); // Meme en attente
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

    public function getPercent(): ?float
    {
        return round($this->rembourse / $this->frais, 4) * 100;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateDecision(): ?\DateTimeInterface
    {
        return $this->dateDecision;
    }

    public function setDateDecision(\DateTimeInterface $dateDecision): self
    {
        $this->dateDecision = $dateDecision;

        return $this;
    }
}
