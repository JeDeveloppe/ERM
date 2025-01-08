<?php

namespace App\Entity;

use App\Repository\CgoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    private ?string $territoryColor = null;

    #[ORM\OneToOne(mappedBy: 'cgo', cascade: ['persist', 'remove'])]
    private ?TelematicArea $telematicArea = null;

    #[ORM\ManyToOne(inversedBy: 'cgos')]
    private ?City $city = null;

    #[ORM\ManyToOne(inversedBy: 'cgos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RegionErm $regionErm = null;

    #[ORM\Column]
    private ?int $cm = null;

    #[ORM\Column(length: 50)]
    private ?string $zoneName = null;

    /**
     * @var Collection<int, Shop>
     */
    #[ORM\ManyToMany(targetEntity: Shop::class, inversedBy: 'cgos')]
    private Collection $shopsUnderControls;

    #[ORM\ManyToOne(inversedBy: 'cgos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manager $manager = null;

    #[ORM\ManyToOne(inversedBy: 'cgos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ShopClass $classErm = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    public function __construct()
    {
        $this->shopsUnderControls = new ArrayCollection();
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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
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

    public function getCm(): ?int
    {
        return $this->cm;
    }

    public function setCm(int $cm): static
    {
        $this->cm = $cm;

        return $this;
    }

    public function getZoneName(): ?string
    {
        return $this->zoneName;
    }

    public function setZoneName(string $zoneName): static
    {
        $this->zoneName = $zoneName;

        return $this;
    }

    /**
     * @return Collection<int, Shop>
     */
    public function getShopsUnderControls(): Collection
    {
        return $this->shopsUnderControls;
    }

    public function addShopsUnderControl(Shop $shopsUnderControl): static
    {
        if (!$this->shopsUnderControls->contains($shopsUnderControl)) {
            $this->shopsUnderControls->add($shopsUnderControl);
        }

        return $this;
    }

    public function removeShopsUnderControl(Shop $shopsUnderControl): static
    {
        $this->shopsUnderControls->removeElement($shopsUnderControl);

        return $this;
    }

    public function __toString()
    {
        return $this->name.' '.$this->cm;
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

    public function getClassErm(): ?ShopClass
    {
        return $this->classErm;
    }

    public function setClassErm(?ShopClass $classErm): static
    {
        $this->classErm = $classErm;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }
}
