<?php

namespace App\Entity;

use App\Repository\ShopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'shops')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ZoneErm $zoneErm = null;

    #[ORM\ManyToOne(inversedBy: 'shops')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ShopClass $shopClass = null;

    #[ORM\Column]
    private ?int $cm = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\ManyToOne(inversedBy: 'shops')]
    private ?City $city = null;

    #[ORM\Column(length: 25)]
    private ?string $phone = null;

    /**
     * @var Collection<int, Cgo>
     */
    #[ORM\ManyToMany(targetEntity: Cgo::class, mappedBy: 'shopsUnderControls')]
    private Collection $cgos;

    #[ORM\ManyToOne(inversedBy: 'shops')]
    private ?Manager $manager = null;

    public function __construct()
    {
        $this->cgos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getShopClass(): ?ShopClass
    {
        return $this->shopClass;
    }

    public function setShopClass(?ShopClass $shopClass): static
    {
        $this->shopClass = $shopClass;

        return $this;
    }

    public function getCm(): ?int
    {
        return $this->cm;
    }

    public function setCm(int $cm): static
    {
        $this->cm = $cm;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

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

    public function __toString()
    {
        return $this->name .' ('.$this->cm.')';
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
            $cgo->addShopsUnderControl($this);
        }

        return $this;
    }

    public function removeCgo(Cgo $cgo): static
    {
        if ($this->cgos->removeElement($cgo)) {
            $cgo->removeShopsUnderControl($this);
        }

        return $this;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(?Manager $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

}
