<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(
        pattern: '/^(?:0)[1-9](?:[\s.-]*\d{2}){4}$/',
        message: 'Le numéro de téléphone n\'est pas au format valide. Il doit commencer par 0 et être composé de 10 chiffres.'
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(
        message: 'L\'email "{{ value }}" n\'est pas un email valide.'
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $informations = null;

    /**
     * @var Collection<int, TechnicianFormations>
     */
    #[ORM\ManyToMany(targetEntity: TechnicianFormations::class, inversedBy: 'people')]
    private Collection $technicianFormations;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Shop $shop = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?TechnicianVehicle $vehicle = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Cgo $controledByCgo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $zoneColor = null;

    /**
     * L'AO qui encadre ce CT (rôle CT uniquement) - auto-référencé maintenant que
     * Manager a disparu.
     */
    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Person $manager = null;

    /**
     * @var Collection<int, Shop>
     */
    #[ORM\ManyToMany(targetEntity: Shop::class, inversedBy: 'peopleWorkingForMe')]
    #[ORM\JoinTable(name: 'person_work_for_shop')]
    private Collection $workForShops;

    /**
     * @var Collection<int, TechnicianFonction>
     */
    #[ORM\ManyToMany(targetEntity: TechnicianFonction::class, inversedBy: 'people')]
    private Collection $fonctions;

    /**
     * @var Collection<int, RoleErm>
     */
    #[ORM\ManyToMany(targetEntity: RoleErm::class, inversedBy: 'people')]
    #[ORM\JoinTable(name: 'person_role_erm')]
    private Collection $roles;

    /**
     * Région ERM que cette personne dirige (rôle DR).
     */
    #[ORM\OneToOne(inversedBy: 'regionManagers', cascade: ['persist', 'remove'])]
    private ?RegionErm $regionErm = null;

    /**
     * Zones ERM que cette personne gère (rôles AO/RZ/RAVL/DR).
     *
     * @var Collection<int, ZoneErm>
     */
    #[ORM\OneToMany(targetEntity: ZoneErm::class, mappedBy: 'manager')]
    private Collection $zoneErms;

    /**
     * CGO que cette personne gère (rôles RCGO VI/RCGO VL).
     *
     * @var Collection<int, Cgo>
     */
    #[ORM\OneToMany(targetEntity: Cgo::class, mappedBy: 'manager')]
    private Collection $cgos;

    /**
     * Autres personnes dont cette personne est le manager (ex: CT dont elle est l'AO).
     *
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'manager')]
    private Collection $people;

    #[ORM\ManyToOne(inversedBy: 'peopleUpdated')]
    private ?User $updatedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->technicianFormations = new ArrayCollection();
        $this->fonctions = new ArrayCollection();
        $this->workForShops = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->zoneErms = new ArrayCollection();
        $this->cgos = new ArrayCollection();
        $this->people = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        if(empty($this->phone)){
            return 'Tél. non renseigné';
        }else{
            return $this->phone;
        }
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

    public function getInformations(): ?string
    {
        return $this->informations;
    }

    public function setInformations(?string $informations): static
    {
        $this->informations = $informations;

        return $this;
    }

    /**
     * @return Collection<int, TechnicianFormations>
     */
    public function getTechnicianFormations(): Collection
    {
        return $this->technicianFormations;
    }

    public function addTechnicianFormation(TechnicianFormations $personFormation): static
    {
        if (!$this->technicianFormations->contains($personFormation)) {
            $this->technicianFormations->add($personFormation);
        }

        return $this;
    }

    public function removeTechnicianFormation(TechnicianFormations $personFormation): static
    {
        $this->technicianFormations->removeElement($personFormation);

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getVehicle(): ?TechnicianVehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?TechnicianVehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getControledByCgo(): ?Cgo
    {
        return $this->controledByCgo;
    }

    public function setControledByCgo(?Cgo $controledByCgo): static
    {
        $this->controledByCgo = $controledByCgo;

        return $this;
    }

    /**
     * @return Collection<int, TechnicianFonction>
     */
    public function getFonctions(): Collection
    {
        return $this->fonctions;
    }

    public function addFonction(TechnicianFonction $fonction): static
    {
        if (!$this->fonctions->contains($fonction)) {
            $this->fonctions->add($fonction);
        }

        return $this;
    }

    public function removeFonction(TechnicianFonction $fonction): static
    {
        $this->fonctions->removeElement($fonction);

        return $this;
    }

    public function getNameAndFirstName(): ?string
    {
        // Concatène le nom et le prénom
        $fullName = trim($this->getName() . ' ' . $this->getFirstName());

        // Si la chaîne est vide, retourne null
        if (empty($fullName)) {
            return 'Tech. non renseigné';
        }

        // Sinon, retourne le nom complet
        return $fullName;
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

    public function getZoneColor(): ?string
    {
        return $this->zoneColor;
    }

    public function setZoneColor(?string $zoneColor): static
    {
        $this->zoneColor = $zoneColor;

        return $this;
    }

    public function getManager(): ?self
    {
        return $this->manager;
    }

    public function setManager(?self $manager): static
    {
        $this->manager = $manager;

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

    /**
     * @return Collection<int, RoleErm>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(RoleErm $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function removeRole(RoleErm $role): static
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function hasRole(string $roleName): bool
    {
        foreach ($this->roles as $role) {
            if ($role->getName() === $roleName) {
                return true;
            }
        }

        return false;
    }

    public function getRoleNames(): string
    {
        return implode(', ', array_map(fn(RoleErm $role) => $role->getName(), $this->roles->toArray()));
    }

    public function getWorkForShopsNames(): string
    {
        return implode(', ', array_map(fn(Shop $shop) => $shop->getName(), $this->workForShops->toArray()));
    }

    public function getRegionErm(): ?RegionErm
    {
        return $this->regionErm;
    }

    public function setRegionErm(?RegionErm $regionErm): static
    {
        // unset the owning side of the relation if necessary
        if ($regionErm === null && $this->regionErm !== null) {
            $this->regionErm->setRegionManagers(null);
        }

        // set the owning side of the relation if necessary
        if ($regionErm !== null && $regionErm->getRegionManagers() !== $this) {
            $regionErm->setRegionManagers($this);
        }

        $this->regionErm = $regionErm;

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
            if ($zoneErm->getManager() === $this) {
                $zoneErm->setManager(null);
            }
        }

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
            $cgo->setManager($this);
        }

        return $this;
    }

    public function removeCgo(Cgo $cgo): static
    {
        if ($this->cgos->removeElement($cgo)) {
            if ($cgo->getManager() === $this) {
                $cgo->setManager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->setManager($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->people->removeElement($person)) {
            if ($person->getManager() === $this) {
                $person->setManager(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getNameAndFirstName() ?? '';
    }   
}
