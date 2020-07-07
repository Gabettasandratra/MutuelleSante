<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\HistoriqueCotisationRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=HistoriqueCotisationRepository::class)
 * @UniqueEntity(fields={"reference"}, message="Ce réference de paiement a été déja enregistrer")
 */
class HistoriqueCotisation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive()
     */
    private $montant;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("+1 day")
     */
    private $datePaiement;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity=CompteCotisation::class, inversedBy="historiqueCotisations", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteCotisation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remarque;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tresorerie;

    /**
     * @ORM\OneToOne(targetEntity=Article::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $article;

    public function __construct()
    {
        $this->datePaiement = new \DateTime();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getCompteCotisation(): ?CompteCotisation
    {
        return $this->compteCotisation;
    }

    public function setCompteCotisation(?CompteCotisation $compteCotisation): self
    {
        $this->compteCotisation = $compteCotisation;

        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): self
    {
        $this->remarque = $remarque;

        return $this;
    }

    public function getTresorerie(): ?Compte
    {
        return $this->tresorerie;
    }

    public function setTresorerie(?Compte $tresorerie): self
    {
        $this->tresorerie = $tresorerie;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}
