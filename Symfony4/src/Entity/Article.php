<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;

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
     */
    private $montant;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $piece;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $analytique;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $moyen;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteDebit;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class, inversedBy="articles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteCredit;

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

    public function getMoyen(): ?string
    {
        return $this->moyen;
    }

    public function setMoyen(string $moyen): self
    {
        $this->moyen = $moyen;

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
}
