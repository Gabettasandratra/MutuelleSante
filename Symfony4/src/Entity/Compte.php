<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompteRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CompteRepository::class)
 * @UniqueEntity(fields={"poste"}, message="Ce numéro de compte existe déja")
 * @UniqueEntity(fields={"codeJournal"}, message="Le code {{ value }} est déja utiliser")
 */
class Compte
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     * @Assert\Regex("/^[1-7][0-9]{1,5}$/")
     */
    private $poste;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\Column(type="boolean")
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isTresor;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $classe;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, unique=true)
     */
    private $codeJournal;

    /**
     * @ORM\Column(type="boolean")
     */
    private $acceptOut;

    /**
     * @ORM\Column(type="boolean")
     */
    private $acceptIn;

    public function __construct()
    {
        $this->acceptOut = true;
        $this->acceptIn = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(string $poste): self
    {
        
        $this->poste = str_pad($poste, 6, "0");

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

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

    public function getType(): ?bool
    {
        return $this->type;
    }

    public function setType(bool $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsTresor(): ?bool
    {
        return $this->isTresor;
    }

    public function setIsTresor(bool $isTresor): self
    {
        $this->isTresor = $isTresor;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getClasse(): ?string
    {
        return $this->classe;
    }

    public function setClasse(string $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getCodeJournal(): ?string
    {
        return $this->codeJournal;
    }

    public function setCodeJournal(string $codeJournal): self
    {
        $this->codeJournal = strtoupper($codeJournal);

        return $this;
    }

    public function getAcceptOut(): ?bool
    {
        return $this->acceptOut;
    }

    public function setAcceptOut(bool $acceptOut): self
    {
        $this->acceptOut = $acceptOut;

        return $this;
    }

    public function getAcceptIn(): ?bool
    {
        return $this->acceptIn;
    }

    public function setAcceptIn(bool $acceptIn): self
    {
        $this->acceptIn = $acceptIn;

        return $this;
    }

    /**
     * Verifie si le compte de trésorerie est un compte de chèque
     */
    public function isTresorerieCheque()
    {
        return str_split($this->poste, 4)[0] == "5112";
    }

}
