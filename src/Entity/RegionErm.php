<?php

namespace App\Entity;

use App\Repository\RegionErmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegionErmRepository::class)]
class RegionErm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ZoneErm>
     */
    #[ORM\OneToMany(targetEntity: ZoneErm::class, mappedBy: 'regionErm')]
    private Collection $zoneErms;

    #[ORM\OneToOne(mappedBy: 'regionErm', cascade: ['persist', 'remove'])]
    private ?Manager $regionManagers = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $territoryColor = null;

    public function __construct()
    {
        $this->zoneErms = new ArrayCollection();
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
     * @return Collection<int, ZoneErm>
     */
    public function getZoneErms(): Collection
    {
        return $this->zoneErms;
    }

    public function addZoneErm(ZoneErm $zoneErm): static
    {
        if (!$this->zoneErms->contains($zoneErm)) {
            $this->zoneErms->add($zoneErm);
            $zoneErm->setRegionErm($this);
        }

        return $this;
    }

    public function removeZoneErm(ZoneErm $zoneErm): static
    {
        if ($this->zoneErms->removeElement($zoneErm)) {
            // set the owning side to null (unless already changed)
            if ($zoneErm->getRegionErm() === $this) {
                $zoneErm->setRegionErm(null);
            }
        }

        return $this;
    }

    public function getRegionManagers(): ?Manager
    {
        return $this->regionManagers;
    }

    public function setRegionManagers(?Manager $regionManagers): static
    {
        // unset the owning side of the relation if necessary
        if ($regionManagers === null && $this->regionManagers !== null) {
            $this->regionManagers->setRegionErm(null);
        }

        // set the owning side of the relation if necessary
        if ($regionManagers !== null && $regionManagers->getRegionErm() !== $this) {
            $regionManagers->setRegionErm($this);
        }

        $this->regionManagers = $regionManagers;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getTerritoryColor(): ?string
    {
        return $this->territoryColor;
    }

    public function setTerritoryColor(?string $territoryColor): static
    {
        $this->territoryColor = $territoryColor;

        return $this;
    }
}
