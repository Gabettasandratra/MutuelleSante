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
 * @UniqueEntity(fields={"codeMutuelle"}, message="Code mutuelle existe déja")
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
     * @ORM\Column(type="string", length=20, unique=true)
     * @Assert\Length(min=3,max=10)
     */
    private $codeMutuelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sexe;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\LessThan("-18 years")
     */
    private $dateNaissance;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $profession;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $salaire;

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
     * @ORM\OneToMany(targetEntity=EtatAdherent::class, mappedBy="adherent", orphanRemoval=true)
     */
    private $etatAdherents;

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
     * @ORM\ManyToOne(targetEntity=Garantie::class, inversedBy="adherents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $garantie;

    /**
     * @ORM\Column(type="array")
     */
    private $tailleFamille = [];

    /**
     * @ORM\OneToMany(targetEntity=CotisationEmise::class, mappedBy="adherent", orphanRemoval=true)
     */
    private $cotisationEmises;

    /**
     * @ORM\OneToMany(targetEntity=CotisationPercue::class, mappedBy="adherent", orphanRemoval=true)
     */
    private $cotisationPercues;

    public function __construct()
    {
        $this->etatAdherents = new ArrayCollection();
        $this->pacs = new ArrayCollection();
        $this->cotisationEmises = new ArrayCollection();
        $this->cotisationPercues = new ArrayCollection();
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

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getSalaire(): ?float
    {
        return $this->salaire;
    }

    public function setSalaire(?float $salaire): self
    {
        $this->salaire = $salaire;

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
     * @return Collection|EtatAdherent[]
     */
    public function getEtatAdherents(): Collection
    {
        return $this->etatAdherents;
    }

    public function addEtatAdherent(EtatAdherent $etatAdherent): self
    {
        if (!$this->etatAdherents->contains($etatAdherent)) {
            $this->etatAdherents[] = $etatAdherent;
            $etatAdherent->setAdherent($this);
        }

        return $this;
    }

    public function removeEtatAdherent(EtatAdherent $etatAdherent): self
    {
        if ($this->etatAdherents->contains($etatAdherent)) {
            $this->etatAdherents->removeElement($etatAdherent);
            // set the owning side to null (unless already changed)
            if ($etatAdherent->getAdherent() === $this) {
                $etatAdherent->setAdherent(null);
            }
        }

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

    public function getGarantie(): ?Garantie
    {
        return $this->garantie;
    }

    public function setGarantie(?Garantie $garantie): self
    {
        $this->garantie = $garantie;

        return $this;
    }

    public function getTailleFamille(): ?array
    {
        return $this->tailleFamille;
    }

    public function setTailleFamille(array $tailleFamille): self
    {
        $this->tailleFamille = $tailleFamille;

        return $this;
    }

    public function getValueTailleFamille($m = null)
    {
        if ($m == null) {
            $m = date('m');
        }
        return $this->tailleFamille[$m-1];
    }

    public function getStatus()
    {
        $debObs =   new \DateTime(date("Y-m-d H:i:s", $this->dateInscription->getTimestamp()));
        $obs = $this->garantie->getPeriodeObservation();
        $finObs = $this->dateInscription;
        date_add($finObs, date_interval_create_from_date_string("$obs months"));
        $today = new \DateTime();

        if($today->getTimestamp() > $debObs->getTimestamp() && $today->getTimestamp() < $finObs->getTimestamp()) {
            return "En période d'observation";
        } else {
            return "En cours de droit";
        }   
        
    }

    /**
     * @return Collection|CotisationEmise[]
     */
    public function getCotisationEmises(): Collection
    {
        return $this->cotisationEmises;
    }

    public function getCurrentCotisationEmise(): CotisationEmise
    {
        $currentYear = date('Y');
        foreach ($this->cotisationEmises as $cotisationEmise) {
            if ($cotisationEmise->getExercice()->getAnnee() == $currentYear) {
                return $cotisationEmise;
            }
        }
        return null;
    }

    // After updating the taille famille 
    public function updateCurrentCotisationEmise()
    {
        $currentCotisationEmise = $this->getCurrentCotisationEmise();
        $montant1 = $this->getGarantie()->getMontant1();
        $cotisations = $currentCotisationEmise->getCotisations();
        // Option 2 : Montant unique par béneficiaires
        foreach ($this->tailleFamille as $m => $n) {
            $cotisations[$m] = $n * $montant1;
        } 
        $currentCotisationEmise->setCotisations($cotisations);
    }

    public function addCotisationEmise(CotisationEmise $cotisationEmise): self
    {
        if (!$this->cotisationEmises->contains($cotisationEmise)) {
            $this->cotisationEmises[] = $cotisationEmise;
            $cotisationEmise->setAdherent($this);
        }

        return $this;
    }

    public function removeCotisationEmise(CotisationEmise $cotisationEmise): self
    {
        if ($this->cotisationEmises->contains($cotisationEmise)) {
            $this->cotisationEmises->removeElement($cotisationEmise);
            // set the owning side to null (unless already changed)
            if ($cotisationEmise->getAdherent() === $this) {
                $cotisationEmise->setAdherent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CotisationPercue[]
     */
    public function getCotisationPercues(): Collection
    {
        return $this->cotisationPercues;
    }

    public function getCurrentCotisationPercue(): CotisationPercue
    {
        $currentYear = date('Y');
        foreach ($this->cotisationPercues as $cotisationPercue) {
            if ($cotisationPercue->getExercice()->getAnnee() == $currentYear) {
                return $cotisationPercue;
            }
        }
        return null;
    }

    public function addCotisationPercue(CotisationPercue $cotisationPercue): self
    {
        if (!$this->cotisationPercues->contains($cotisationPercue)) {
            $this->cotisationPercues[] = $cotisationPercue;
            $cotisationPercue->setAdherent($this);
        }

        return $this;
    }

    public function removeCotisationPercue(CotisationPercue $cotisationPercue): self
    {
        if ($this->cotisationPercues->contains($cotisationPercue)) {
            $this->cotisationPercues->removeElement($cotisationPercue);
            // set the owning side to null (unless already changed)
            if ($cotisationPercue->getAdherent() === $this) {
                $cotisationPercue->setAdherent(null);
            }
        }

        return $this;
    }
}
