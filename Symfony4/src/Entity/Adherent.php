<?php

namespace App\Entity;

use App\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=AdherentRepository::class)
 * @UniqueEntity(fields={"numero"}, message="Le numéro de congrégation donné existe déja")
 */
class Adherent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telephone1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telephone2;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("+1 day")
     */
    private $dateInscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\OneToMany(targetEntity=Pac::class, mappedBy="adherent", orphanRemoval=true)
     */
    private $pacs;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=HistoriqueCotisation::class, mappedBy="adherent", orphanRemoval=true)
     */
    private $historiqueCotisations;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $numero;

    // temporary
    private $nbNouveau;
    private $nbAncien;


    public function __construct()
    {
        $this->dateInscription = new \DateTime();
        $this->pacs = new ArrayCollection();
        $this->historiqueCotisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone1(): ?string
    {
        return $this->telephone1;
    }

    public function setTelephone1(string $telephone1): self
    {
        $this->telephone1 = $telephone1;

        return $this;
    }

    public function getTelephone2(): ?string
    {
        return $this->telephone2;
    }

    public function setTelephone2(?string $telephone2): self
    {
        $this->telephone2 = $telephone2;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): self
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection|Pac[]
     */
    public function getPacs(): Collection
    {
        return $this->pacs;
    }

    public function addPac(Pac $pac): self
    {
        if (!$this->pacs->contains($pac)) {
            $this->pacs[] = $pac;
            $pac->setAdherent($this);
        }

        return $this;
    }

    public function removePac(Pac $pac): self
    {
        if ($this->pacs->contains($pac)) {
            $this->pacs->removeElement($pac);
            // set the owning side to null (unless already changed)
            if ($pac->getAdherent() === $this) {
                $pac->setAdherent(null);
            }
        }

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|HistoriqueCotisation[]
     */
    public function getHistoriqueCotisations(): Collection
    {
        return $this->historiqueCotisations;
    }

    public function addHistoriqueCotisation(HistoriqueCotisation $historiqueCotisation): self
    {
        if (!$this->historiqueCotisations->contains($historiqueCotisation)) {
            $this->historiqueCotisations[] = $historiqueCotisation;
            $historiqueCotisation->setAdherent($this);
        }

        return $this;
    }

    public function removeHistoriqueCotisation(HistoriqueCotisation $historiqueCotisation): self
    {
        if ($this->historiqueCotisations->contains($historiqueCotisation)) {
            $this->historiqueCotisations->removeElement($historiqueCotisation);
            // set the owning side to null (unless already changed)
            if ($historiqueCotisation->getAdherent() === $this) {
                $historiqueCotisation->setAdherent(null);
            }
        }

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNbNouveau(): ?int
    {
        return $this->nbNouveau;
    }

    public function setNbNouveau(int $n): self
    {
        $this->nbNouveau = $n;

        return $this;
    }

    public function getNbAncien(): ?int
    {
        return $this->nbAncien;
    }

    public function setNbAncien(int $n): self
    {
        $this->nbAncien = $n;

        return $this;
    }
}
