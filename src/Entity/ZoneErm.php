<?php

namespace App\Entity;

use App\Repository\ZoneErmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZoneErmRepository::class)]
class ZoneErm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'zoneErms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RegionErm $regionErm = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Shop>
     */
    #[ORM\OneToMany(targetEntity: Shop::class, mappedBy: 'zoneErm')]
    private Collection $shops;

    #[ORM\OneToOne(mappedBy: 'zoneErm', cascade: ['persist', 'remove'])]
    private ?Manager $zoneManagers = null;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegionErm(): ?RegionErm
    {
        return $this->regionErm;
    }

    public function setRegionErm(?RegionErm $regionErm): static
    {
        $this->regionErm = $regionErm;

        return $this;
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
     * @return Collection<int, Shop>
     */
    public function getShops(): Collection
    {
        return $this->shops;
    }

    public function addShop(Shop $shop): static
    {
        if (!$this->shops->contains($shop)) {
            $this->shops->add($shop);
            $shop->setZoneErm($this);
        }

        return $this;
    }

    public function removeShop(Shop $shop): static
    {
        if ($this->shops->removeElement($shop)) {
            // set the owning side to null (unless already changed)
            if ($shop->getZoneErm() === $this) {
                $shop->setZoneErm(null);
            }
        }

        return $this;
    }

    public function getZoneManagers(): ?Manager
    {
        return $this->zoneManagers;
    }

    public function setZoneManagers(?Manager $zoneManagers): static
    {
        // unset the owning side of the relation if necessary
        if ($zoneManagers === null && $this->zoneManagers !== null) {
            $this->zoneManagers->setZoneErm(null);
        }

        // set the owning side of the relation if necessary
        if ($zoneManagers !== null && $zoneManagers->getZoneErm() !== $this) {
            $zoneManagers->setZoneErm($this);
        }

        $this->zoneManagers = $zoneManagers;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
