<?php

namespace App\Entity;

use App\Repository\ManagerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManagerRepository::class)]
class Manager
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'regionManagers', cascade: ['persist', 'remove'])]
    private ?RegionErm $regionErm = null;

    #[ORM\OneToOne(inversedBy: 'zoneManagers', cascade: ['persist', 'remove'])]
    private ?ZoneErm $zoneErm = null;

    /**
     * @var Collection<int, Shop>
     */
    #[ORM\OneToMany(targetEntity: Shop::class, mappedBy: 'manager')]
    private Collection $shop;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToOne(mappedBy: 'manager', cascade: ['persist', 'remove'])]
    private ?Cgo $cgo = null;

    #[ORM\ManyToOne(inversedBy: 'managers')]
    private ?ManagerClass $managerClass = null;

    /**
     * @var Collection<int, ZoneErm>
     */
    #[ORM\OneToMany(targetEntity: ZoneErm::class, mappedBy: 'manager')]
    private Collection $zoneErms;

    public function __construct()
    {
        $this->shop = new ArrayCollection();
        $this->zoneErms = new ArrayCollection();
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

    public function getZoneErm(): ?ZoneErm
    {
        return $this->zoneErm;
    }

    public function setZoneErm(?ZoneErm $zoneErm): static
    {
        $this->zoneErm = $zoneErm;

        return $this;
    }

    /**
     * @return Collection<int, Shop>
     */
    public function getShop(): Collection
    {
        return $this->shop;
    }

    public function addShop(Shop $shop): static
    {
        if (!$this->shop->contains($shop)) {
            $this->shop->add($shop);
            $shop->setManager($this);
        }

        return $this;
    }

    public function removeShop(Shop $shop): static
    {
        if ($this->shop->removeElement($shop)) {
            // set the owning side to null (unless already changed)
            if ($shop->getManager() === $this) {
                $shop->setManager(null);
            }
        }

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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

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

    public function getCgo(): ?Cgo
    {
        return $this->cgo;
    }

    public function setCgo(Cgo $cgo): static
    {
        // set the owning side of the relation if necessary
        if ($cgo->getManager() !== $this) {
            $cgo->setManager($this);
        }

        $this->cgo = $cgo;

        return $this;
    }

    public function getManagerClass(): ?ManagerClass
    {
        return $this->managerClass;
    }

    public function setManagerClass(?ManagerClass $managerClass): static
    {
        $this->managerClass = $managerClass;

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
            $zoneErm->setManager($this);
        }

        return $this;
    }

    public function removeZoneErm(ZoneErm $zoneErm): static
    {
        if ($this->zoneErms->removeElement($zoneErm)) {
            // set the owning side to null (unless already changed)
            if ($zoneErm->getManager() === $this) {
                $zoneErm->setManager(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->firstName.' '.$this->lastName;
    }
}
