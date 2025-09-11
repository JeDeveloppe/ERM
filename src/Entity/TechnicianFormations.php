<?php

namespace App\Entity;

use App\Repository\TechnicianFormationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechnicianFormationsRepository::class)]
class TechnicianFormations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Technician>
     */
    #[ORM\ManyToMany(targetEntity: Technician::class, mappedBy: 'technicianFormations')]
    private Collection $technicians;

    #[ORM\Column(length: 10)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'technicianFormationsUpdated')]
    private ?User $updatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->technicians = new ArrayCollection();
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

    /**
     * @return Collection<int, Technician>
     */
    public function getTechnicians(): Collection
    {
        return $this->technicians;
    }

    public function addTechnician(Technician $technician): static
    {
        if (!$this->technicians->contains($technician)) {
            $this->technicians->add($technician);
            $technician->addTechnicianFormation($this);
        }

        return $this;
    }

    public function removeTechnician(Technician $technician): static
    {
        if ($this->technicians->removeElement($technician)) {
            $technician->removeTechnicianFormation($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
