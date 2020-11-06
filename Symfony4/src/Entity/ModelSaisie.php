<?php

namespace App\Entity;

use App\Repository\ModelSaisieRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ModelSaisieRepository::class)
 */
class ModelSaisie
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
    private $journal;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class)
     */
    private $debit;

    /**
     * @ORM\ManyToOne(targetEntity=Compte::class)
     */
    private $credit;

    /**
     * @ORM\ManyToOne(targetEntity=Analytique::class)
     */
    private $analytic;

    /**
     * @ORM\ManyToOne(targetEntity=Tier::class)
     */
    private $tier;

    /**
     * @ORM\ManyToOne(targetEntity=Budget::class)
     */
    private $budget;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJournal(): ?string
    {
        return $this->journal;
    }

    public function setJournal(string $journal): self
    {
        $this->journal = $journal;

        return $this;
    }

    public function getDebit(): ?Compte
    {
        return $this->debit;
    }

    public function setDebit(?Compte $debit): self
    {
        $this->debit = $debit;

        return $this;
    }

    public function getCredit(): ?Compte
    {
        return $this->credit;
    }

    public function setCredit(?Compte $credit): self
    {
        $this->credit = $credit;

        return $this;
    }

    public function getAnalytic(): ?Analytique
    {
        return $this->analytic;
    }

    public function setAnalytic(?Analytique $analytic): self
    {
        $this->analytic = $analytic;

        return $this;
    }

    public function getTier(): ?Tier
    {
        return $this->tier;
    }

    public function setTier(?Tier $tier): self
    {
        $this->tier = $tier;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;

        return $this;
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
}
