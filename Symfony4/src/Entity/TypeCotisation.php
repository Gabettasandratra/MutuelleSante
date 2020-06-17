<?php

namespace App\Entity;

use App\Repository\TypeCotisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeCotisationRepository::class)
 */
class TypeCotisation
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
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Garantie::class, mappedBy="typeCotisation")
     */
    private $garanties;

    public function __construct()
    {
        $this->garanties = new ArrayCollection();
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

    /**
     * @return Collection|Garantie[]
     */
    public function getGaranties(): Collection
    {
        return $this->garanties;
    }

    public function addGaranty(Garantie $garanty): self
    {
        if (!$this->garanties->contains($garanty)) {
            $this->garanties[] = $garanty;
            $garanty->setTypeCotisation($this);
        }

        return $this;
    }

    public function removeGaranty(Garantie $garanty): self
    {
        if ($this->garanties->contains($garanty)) {
            $this->garanties->removeElement($garanty);
            // set the owning side to null (unless already changed)
            if ($garanty->getTypeCotisation() === $this) {
                $garanty->setTypeCotisation(null);
            }
        }

        return $this;
    }
}
