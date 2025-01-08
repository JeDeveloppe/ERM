<?php

namespace App\Entity;

use App\Repository\TelematicAreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelematicAreaRepository::class)]
class TelematicArea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'telematicArea', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cgo $cgo = null;

    /**
     * @var Collection<int, Department>
     */
    #[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'telematicArea')]
    private Collection $departments;

    #[ORM\Column(length: 20)]
    private ?string $territoryColor = null;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCgo(): ?Cgo
    {
        return $this->cgo;
    }

    public function setCgo(Cgo $cgo): static
    {
        $this->cgo = $cgo;

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setTelematicArea($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getTelematicArea() === $this) {
                $department->setTelematicArea(null);
            }
        }

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

    public function __toString()
    {
        return $this->cgo->getName();
    }
}
