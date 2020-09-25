<?php

namespace App\Entity;

use App\Entity\Exercice;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdherentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=AdherentRepository::class)
 * @UniqueEntity(fields={"numero"}, message="Le numéro de congrégation {{ value }} existe déja")
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
     * @Assert\Regex("/^(\+261|0)3[2,3,4,9][0-9]{7}$/")
     * @Assert\NotBlank
     */
    private $telephone1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex("/^(\+261|0)3[2,3,4,9][0-9]{7}$/")
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
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @Assert\Regex("/^[0-9]*$/")
     */
    private $numero;

    /**
     * @ORM\OneToMany(targetEntity=CompteCotisation::class, mappedBy="adherent", fetch="EAGER")
     */
    private $compteCotisations;

    /**
     * @ORM\OneToMany(targetEntity=Prestation::class, mappedBy="adherent")
     */
    private $prestations;

    /**
     * @ORM\OneToMany(targetEntity=Remboursement::class, mappedBy="adherent")
     */
    private $remboursements;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codeAnalytique;


    public function __construct()
    {   
        $this->dateInscription = new \DateTime();
        $this->pacs = new ArrayCollection();
        $this->compteCotisations = new ArrayCollection();
        $this->prestations = new ArrayCollection();
        $this->remboursements = new ArrayCollection();
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

    public function getTelephone(): ?string
    {
        if ($this->telephone2) {
            return $this->telephone1.'/'.$this->telephone2;
        }
        return $this->telephone1;
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

    public function getNumero()
    {
        return str_pad((string)$this->numero, 3, "0", 0);
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * @return Collection|CompteCotisation[]
     */
    public function getCompteCotisations(): Collection
    {
        return $this->compteCotisations;
    }

    public function getCompteCotisation(Exercice $exercice)
    {        
        foreach ($this->compteCotisations as  $compteCotisation) {
            if ( $compteCotisation->getExercice() === $exercice) {
                return $compteCotisation;
            }
        }
        return null;
    }

    // l'exercice courant
    public function getCurrentCompteCotisation()
    {       
        $now = new \DateTime();
        $exercice = null;
        foreach ($this->compteCotisations as  $compteCotisation) {
            $exercice = $compteCotisation->getExercice();
            if ( $exercice->getDateDebut() <= $now && $exercice->getDateFin() >= $now) {
                return $compteCotisation;
            }
        }
        return null;
    }


    public function verifyPrevCompteCotisation(Exercice $exercice)
    {
        foreach ($this->compteCotisations as  $compteCotisation) {
            if ( $compteCotisation->getExercice()->getAnnee() < $exercice->getAnnee()) { // A chaque compte de l'année précedente
                if (!$compteCotisation->getIsPaye()) { // si non payé
                    return $compteCotisation; // On retourne l'année non payé
                }
            }
        }
        return null;
    }

    public function addCompteCotisation(CompteCotisation $compteCotisation): self
    {
        if (!$this->compteCotisations->contains($compteCotisation)) {
            $this->compteCotisations[] = $compteCotisation;
            $compteCotisation->setAdherent($this);
        }

        return $this;
    }

    public function removeCompteCotisation(CompteCotisation $compteCotisation): self
    {
        if ($this->compteCotisations->contains($compteCotisation)) {
            $this->compteCotisations->removeElement($compteCotisation);
            // set the owning side to null (unless already changed)
            if ($compteCotisation->getAdherent() === $this) {
                $compteCotisation->setAdherent(null);
            }
        }

        return $this;
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
            $prestation->setAdherent($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        if ($this->prestations->contains($prestation)) {
            $this->prestations->removeElement($prestation);
            // set the owning side to null (unless already changed)
            if ($prestation->getAdherent() === $this) {
                $prestation->setAdherent(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Remboursement[]
     */
    public function getRemboursements(): Collection
    {
        return $this->remboursements;      
    }

    public function getRemboursementByExercice(Exercice $exercice)
    {
        $out = [];
        foreach ( $this->remboursements as $remboursement) {
            if ( $remboursement->getExercice()->getAnnee() == $exercice->getAnnee()) {
                $out[] = $remboursement; 
            }
        }
        return $out;
    }

    public function addRemboursement(Remboursement $remboursement): self
    {
        if (!$this->remboursements->contains($remboursement)) {
            $this->remboursements[] = $remboursement;
            $remboursement->setAdherent($this);
        }

        return $this;
    }

    public function removeRemboursement(Remboursement $remboursement): self
    {
        if ($this->remboursements->contains($remboursement)) {
            $this->remboursements->removeElement($remboursement);
            // set the owning side to null (unless already changed)
            if ($remboursement->getAdherent() === $this) {
                $remboursement->setAdherent(null);
            }
        }

        return $this;
    }

    public function getCodeAnalytique(): ?string
    {
        return $this->codeAnalytique;
    }

    public function setCodeAnalytique(?string $codeAnalytique): self
    {
        $this->codeAnalytique = $codeAnalytique;

        return $this;
    } 

    public function getNbBeneficiaires()
    {
        $compteCot = $this->getCurrentCompteCotisation();
        return ($compteCot->getAncien() + $compteCot->getNouveau());
    }
}
