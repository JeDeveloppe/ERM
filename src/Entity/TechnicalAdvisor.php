<?php

namespace App\Entity;

use App\Repository\TechnicalAdvisorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TechnicalAdvisorRepository::class)]
class TechnicalAdvisor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'technicalAdvisors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Manager $manager = null;

    #[ORM\ManyToOne(inversedBy: 'technicalAdvisors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shop $attachmentCenter = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 15)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * @var Collection<int, Shop>
     */
    #[ORM\ManyToMany(targetEntity: Shop::class, inversedBy: 'technicalAdvisorsWorkingForMe')]
    private Collection $workForShops;

    #[ORM\Column(length: 10)]
    private ?string $zoneColor = null;

    public function __construct()
    {
        $this->workForShops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAttachmentCenter(): ?Shop
    {
        return $this->attachmentCenter;
    }

    public function setAttachmentCenter(?Shop $attachmentCenter): static
    {
        $this->attachmentCenter = $attachmentCenter;

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

    /**
     * @return Collection<int, Shop>
     */
    public function getWorkForShops(): Collection
    {
        return $this->workForShops;
    }

    public function addWorkForShop(Shop $workForShop): static
    {
        if (!$this->workForShops->contains($workForShop)) {
            $this->workForShops->add($workForShop);
        }

        return $this;
    }

    public function removeWorkForShop(Shop $workForShop): static
    {
        $this->workForShops->removeElement($workForShop);

        return $this;
    }

    public function getZoneColor(): ?string
    {
        return $this->zoneColor;
    }

    public function setZoneColor(string $zoneColor): static
    {
        $this->zoneColor = $zoneColor;

        return $this;
    }
}
