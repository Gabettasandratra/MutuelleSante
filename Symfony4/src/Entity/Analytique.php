<?php

namespace App\Entity;

use App\Repository\AnalytiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnalytiqueRepository::class)
 */
class Analytique
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
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="analytic")
     */
    private $articles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isServiceSante;


    public function __construct($code, $int)
    {
        $this->code = $code;
        $this->libelle = $int;
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
            $article->setAnalytic($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAnalytic() === $this) {
                $article->setAnalytic(null);
            }
        }

        return $this;
    }

    public function getIsServiceSante(): ?bool
    {
        return $this->isServiceSante;
    }

    public function setIsServiceSante(bool $isServiceSante): self
    {
        $this->isServiceSante = $isServiceSante;

        return $this;
    }
}
