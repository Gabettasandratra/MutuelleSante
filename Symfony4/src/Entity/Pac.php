<?php

namespace App\Entity;

use App\Entity\Exercice;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PacRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=PacRepository::class)
 * @UniqueEntity(fields={"codeMutuelle"}, message="Le numéro matricule {{ value }} existe déja")
 * @UniqueEntity(fields={"cin"}, message="Le N° CIN {{ value }} est déja pris par d'autre béneficiaire")
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
     * @ORM\Column(type="integer", columnDefinition="INT(5) UNSIGNED ZEROFILL" )
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
     * @Assert\NotBlank()
     */
    private $parente;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("+1 day")
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

    /**
     * @ORM\Column(type="string", length=15, nullable=true, unique=true)
     */
    private $cin;

    /**
     * @ORM\OneToMany(targetEntity=Prestation::class, mappedBy="pac")
     */
    private $prestations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tel;

    public function __construct()
    {
        $this->dateEntrer = new \DateTime();
        $this->prestations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeMutuelle(): ?string
    {
        
        return str_pad($this->codeMutuelle, 5, "0", 0);
    }

    public function getMatricule(): ?string
    {
        return $this->adherent->getNumero().'/'.$this->getCodeMutuelle();
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

    public function getNomComplet(): ?string
    {
        return $this->nom.' '.$this->prenom;
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

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(?string $cin): self
    {
        $this->cin = $cin;

        return $this;
    }

    // Logic section
    // test if the pac is nouveau or ancien in a given exercice
    public function isNouveau(Exercice $exercice)
    {
        $dateEntrer = $this->dateEntrer;
        if ($dateEntrer >= $exercice->getDateDebut() && $dateEntrer < $exercice->getDateFin()) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection|Prestation[]
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations[] = $prestation;
            $prestation->setPac($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        if ($this->prestations->contains($prestation)) {
            $this->prestations->removeElement($prestation);
            // set the owning side to null (unless already changed)
            if ($prestation->getPac() === $this) {
                $prestation->setPac(null);
            }
        }

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }
}
