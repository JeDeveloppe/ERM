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



    /**
     * @var Collection<int, Department>
     */
    #[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'telematicArea')]
    private Collection $departments;

    #[ORM\Column(length: 20)]
    private ?string $territoryColor = null;

    /**
     * @var Collection<int, Cgo>
     */
    #[ORM\OneToMany(targetEntity: Cgo::class, mappedBy: 'telematicArea')]
    private Collection $cgos;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->cgos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Cgo>
     */
    public function getCgos(): Collection
    {
        return $this->cgos;
    }

    public function addCgo(Cgo $cgo): static
    {
        if (!$this->cgos->contains($cgo)) {
            $this->cgos->add($cgo);
            $cgo->setTelematicArea($this);
        }

        return $this;
    }

    public function removeCgo(Cgo $cgo): static
    {
        if ($this->cgos->removeElement($cgo)) {
            // set the owning side to null (unless already changed)
            if ($cgo->getTelematicArea() === $this) {
                $cgo->setTelematicArea(null);
            }
        }

        return $this;
    }

}
