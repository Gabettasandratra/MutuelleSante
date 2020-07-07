<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $montant;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $piece;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $analytique;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("+1 day")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $categorie;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteDebit;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotEqualTo(propertyPath="compteDebit", message="Les comptes debit et credit doivent être différentes ")
     */
    private $compteCredit;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isFerme;

    public function __construct()
    {   
        $this->isFerme = false;
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPiece(): ?string
    {
        return $this->piece;
    }

    public function setPiece(string $piece): self
    {
        $this->piece = $piece;

        return $this;
    }

    public function getAnalytique(): ?string
    {
        return $this->analytique;
    }

    public function setAnalytique(string $analytique): self
    {
        $this->analytique = $analytique;

        return $this;
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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getCompteDebit(): ?Compte
    {
        return $this->compteDebit;
    }

    public function setCompteDebit(?Compte $compteDebit): self
    {
        $this->compteDebit = $compteDebit;

        return $this;
    }

    public function getCompteCredit(): ?Compte
    {
        return $this->compteCredit;
    }

    public function setCompteCredit(?Compte $compteCredit): self
    {
        $this->compteCredit = $compteCredit;

        return $this;
    }

    public function getIsFerme(): ?bool
    {
        return $this->isFerme;
    }

    public function setIsFerme(bool $isFerme): self
    {
        $this->isFerme = $isFerme;

        return $this;
    }
}
