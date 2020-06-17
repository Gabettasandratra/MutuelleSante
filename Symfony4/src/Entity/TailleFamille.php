<?php

namespace App\Entity;

use App\Repository\TailleFamilleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TailleFamilleRepository::class)
 */
class TailleFamille
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois1;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois2;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois3;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois4;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois5;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois6;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois7;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois8;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois9;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois10;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois11;

    /**
     * @ORM\Column(type="integer")
     */
    private $mois12;

    /**
     * @ORM\OneToOne(targetEntity=Adherent::class, inversedBy="tailleFamille", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $adherent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMois1(): ?int
    {
        return $this->mois1;
    }

    public function setMois1(int $mois1): self
    {
        $this->mois1 = $mois1;

        return $this;
    }

    public function getMois2(): ?int
    {
        return $this->mois2;
    }

    public function setMois2(int $mois2): self
    {
        $this->mois2 = $mois2;

        return $this;
    }

    public function getMois3(): ?int
    {
        return $this->mois3;
    }

    public function setMois3(int $mois3): self
    {
        $this->mois3 = $mois3;

        return $this;
    }

    public function getMois4(): ?int
    {
        return $this->mois4;
    }

    public function setMois4(int $mois4): self
    {
        $this->mois4 = $mois4;

        return $this;
    }

    public function getMois5(): ?int
    {
        return $this->mois5;
    }

    public function setMois5(int $mois5): self
    {
        $this->mois5 = $mois5;

        return $this;
    }

    public function getMois6(): ?int
    {
        return $this->mois6;
    }

    public function setMois6(int $mois6): self
    {
        $this->mois6 = $mois6;

        return $this;
    }

    public function getMois7(): ?int
    {
        return $this->mois7;
    }

    public function setMois7(int $mois7): self
    {
        $this->mois7 = $mois7;

        return $this;
    }

    public function getMois8(): ?int
    {
        return $this->mois8;
    }

    public function setMois8(int $mois8): self
    {
        $this->mois8 = $mois8;

        return $this;
    }

    public function getMois9(): ?int
    {
        return $this->mois9;
    }

    public function setMois9(int $mois9): self
    {
        $this->mois9 = $mois9;

        return $this;
    }

    public function getMois10(): ?int
    {
        return $this->mois10;
    }

    public function setMois10(int $mois10): self
    {
        $this->mois10 = $mois10;

        return $this;
    }

    public function getMois11(): ?int
    {
        return $this->mois11;
    }

    public function setMois11(int $mois11): self
    {
        $this->mois11 = $mois11;

        return $this;
    }

    public function getMois12(): ?int
    {
        return $this->mois12;
    }

    public function setMois12(int $mois12): self
    {
        $this->mois12 = $mois12;

        return $this;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): self
    {
        $this->adherent = $adherent;

        return $this;
    }
}
