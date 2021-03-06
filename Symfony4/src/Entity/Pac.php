<?php

namespace App\Entity;

use App\Repository\PacRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=PacRepository::class)
 * @UniqueEntity(fields={"codeMutuelle"}, message="Code mutuelle existe déja")
 */
class Pac
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
    private $codeMutuelle;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice({"Masculin", "Feminin"})
     */
    private $sexe;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("today")
     */
    private $dateNaissance;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice({"Conjoint", "Fils", "Fille", "Autre"})
     */
    private $parente;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("today")
     */
    private $dateEntrer;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class, inversedBy="pacs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    /**
     * @ORM\OneToMany(targetEntity=EtatPac::class, mappedBy="pac", orphanRemoval=true)
     */
    private $etatPacs;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSortie;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateSortie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remarque;

    public function __construct()
    {
        $this->etatPacs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeMutuelle(): ?string
    {
        return $this->codeMutuelle;
    }

    public function setCodeMutuelle(string $codeMutuelle): self
    {
        $this->codeMutuelle = $codeMutuelle;

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getParente(): ?string
    {
        return $this->parente;
    }

    public function setParente(string $parente): self
    {
        $this->parente = $parente;

        return $this;
    }

    public function getDateEntrer(): ?\DateTimeInterface
    {
        return $this->dateEntrer;
    }

    public function setDateEntrer(\DateTimeInterface $dateEntrer): self
    {
        $this->dateEntrer = $dateEntrer;

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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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

    /**
     * @return Collection|EtatPac[]
     */
    public function getEtatPacs(): Collection
    {
        return $this->etatPacs;
    }

    public function addEtatPac(EtatPac $etatPac): self
    {
        if (!$this->etatPacs->contains($etatPac)) {
            $this->etatPacs[] = $etatPac;
            $etatPac->setPac($this);
        }

        return $this;
    }

    public function removeEtatPac(EtatPac $etatPac): self
    {
        if ($this->etatPacs->contains($etatPac)) {
            $this->etatPacs->removeElement($etatPac);
            // set the owning side to null (unless already changed)
            if ($etatPac->getPac() === $this) {
                $etatPac->setPac(null);
            }
        }

        return $this;
    }

    public function getIsSortie(): ?bool
    {
        return $this->isSortie;
    }

    public function setIsSortie(bool $isSortie): self
    {
        $this->isSortie = $isSortie;

        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(?\DateTimeInterface $dateSortie): self
    {
        $this->dateSortie = $dateSortie;

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
}
