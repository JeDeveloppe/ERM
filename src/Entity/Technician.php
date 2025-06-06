<?php

namespace App\Entity;

use App\Repository\TechnicianRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechnicianRepository::class)]
class Technician
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $informations = null;

    /**
     * @var Collection<int, TechnicianFormations>
     */
    #[ORM\ManyToMany(targetEntity: TechnicianFormations::class, inversedBy: 'technicians')]
    private Collection $technicianFormations;

    #[ORM\ManyToOne(inversedBy: 'technicians')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shop $shop = null;

    #[ORM\ManyToOne(inversedBy: 'technicians')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TechnicianVehicle $vehicle = null;

    #[ORM\Column]
    private ?bool $isTelematic = null;

    #[ORM\ManyToOne(inversedBy: 'technicians')]
    private ?Cgo $controledByCgo = null;

    public function __construct()
    {
        $this->technicianFormations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getInformations(): ?string
    {
        return $this->informations;
    }

    public function setInformations(?string $informations): static
    {
        $this->informations = $informations;

        return $this;
    }

    /**
     * @return Collection<int, TechnicianFormations>
     */
    public function getTechnicianFormations(): Collection
    {
        return $this->technicianFormations;
    }

    public function addTechnicianFormation(TechnicianFormations $technicianFormation): static
    {
        if (!$this->technicianFormations->contains($technicianFormation)) {
            $this->technicianFormations->add($technicianFormation);
        }

        return $this;
    }

    public function removeTechnicianFormation(TechnicianFormations $technicianFormation): static
    {
        $this->technicianFormations->removeElement($technicianFormation);

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getVehicle(): ?TechnicianVehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?TechnicianVehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getIsTelematic(): ?bool
    {
        return $this->isTelematic;
    }

    public function setIsTelematic(bool $isTelematic): static
    {
        $this->isTelematic = $isTelematic;

        return $this;
    }

    public function getControledByCgo(): ?Cgo
    {
        return $this->controledByCgo;
    }

    public function setControledByCgo(?Cgo $controledByCgo): static
    {
        $this->controledByCgo = $controledByCgo;

        return $this;
    }
}
