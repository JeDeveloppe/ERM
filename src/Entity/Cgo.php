<?php

namespace App\Entity;

use App\Repository\CgoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CgoRepository::class)]
class Cgo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'cgo', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manager $manager = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    private ?string $territoryColor = null;

    #[ORM\OneToOne(mappedBy: 'cgo', cascade: ['persist', 'remove'])]
    private ?TelematicArea $telematicArea = null;

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

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(Manager $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getTerritoryColor(): ?string
    {
        return $this->territoryColor;
    }

    public function setTerritoryColor(string $territoryColor): static
    {
        $this->territoryColor = $territoryColor;

        return $this;
    }

    public function getTelematicArea(): ?TelematicArea
    {
        return $this->telematicArea;
    }

    public function setTelematicArea(TelematicArea $telematicArea): static
    {
        // set the owning side of the relation if necessary
        if ($telematicArea->getCgo() !== $this) {
            $telematicArea->setCgo($this);
        }

        $this->telematicArea = $telematicArea;

        return $this;
    }
}
