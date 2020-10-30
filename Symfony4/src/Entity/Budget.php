<?php

namespace App\Entity;

use App\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BudgetRepository::class)
 */
class Budget
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="float")
     */
    private $montant;

    /**
     * @ORM\Column(type="float")
     */
    private $realise;

    /**
     * @ORM\ManyToOne(targetEntity=Exercice::class, inversedBy="budgets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $exercice;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="budget")
     */
    private $articles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $input;

    public function __construct($code, $lib, $montant,$in)
    {
        $this->code = $code;
        $this->libelle = $lib;
        $this->montant = $montant;
        $this->realise = 0;
        $this->input = boolval($in);
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getRealise(): ?float
    {
        return $this->realise;
    }

    public function setRealise(float $realise): self
    {
        $this->realise = $realise;

        return $this;
    }

    public function getExercice(): ?Exercice
    {
        return $this->exercice;
    }

    public function setExercice(?Exercice $exercice): self
    {
        $this->exercice = $exercice;

        return $this;
    }
    
    public function getAsArray()
    {
        return ['id'=>$this->id,'input'=>$this->input,'code'=>$this->code,'libelle'=>$this->libelle,'montant'=>(float)$this->montant,'realise'=>(float)$this->realise];
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setBudget($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getBudget() === $this) {
                $article->setBudget(null);
            }
        }

        return $this;
    }

    public function getInput(): ?bool
    {
        return $this->input;
    }

    public function setInput(bool $input): self
    {
        $this->input = $input;

        return $this;
    }
}
