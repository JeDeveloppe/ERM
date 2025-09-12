<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastVisitAt = null;

    /**
     * @var Collection<int, Technician>
     */
    #[ORM\OneToMany(targetEntity: Technician::class, mappedBy: 'updatedBy')]
    private Collection $techniciansUpdated;

    /**
     * @var Collection<int, TechnicianFormations>
     */
    #[ORM\OneToMany(targetEntity: TechnicianFormations::class, mappedBy: 'updatedBy')]
    private Collection $technicianFormationsUpdated;

    /**
     * @var Collection<int, TechnicianFonction>
     */
    #[ORM\OneToMany(targetEntity: TechnicianFonction::class, mappedBy: 'updatedBy')]
    private Collection $technicianFonctionsUpdated;

    /**
     * @var Collection<int, TelematicArea>
     */
    #[ORM\OneToMany(targetEntity: TelematicArea::class, mappedBy: 'updatedBy')]
    private Collection $telematicAreaUpdated;

    /**
     * @var Collection<int, TechnicianVehicle>
     */
    #[ORM\OneToMany(targetEntity: TechnicianVehicle::class, mappedBy: 'updatedBy')]
    private Collection $technicianVehiclesUpdated;

    /**
     * @var Collection<int, ApiLog>
     */
    #[ORM\OneToMany(targetEntity: ApiLog::class, mappedBy: 'user')]
    private Collection $apiLogs;

    public function __construct()
    {
        $this->techniciansUpdated = new ArrayCollection();
        $this->technicianFormationsUpdated = new ArrayCollection();
        $this->technicianFonctionsUpdated = new ArrayCollection();
        $this->telematicAreaUpdated = new ArrayCollection();
        $this->technicianVehiclesUpdated = new ArrayCollection();
        $this->apiLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastVisitAt(): ?\DateTimeImmutable
    {
        return $this->lastVisitAt;
    }

    public function setLastVisitAt(\DateTimeImmutable $lastVisitAt): static
    {
        $this->lastVisitAt = $lastVisitAt;

        return $this;
    }

    /**
     * @return Collection<int, Technician>
     */
    public function getTechniciansUpdated(): Collection
    {
        return $this->techniciansUpdated;
    }

    public function addTechniciansUpdated(Technician $techniciansUpdated): static
    {
        if (!$this->techniciansUpdated->contains($techniciansUpdated)) {
            $this->techniciansUpdated->add($techniciansUpdated);
            $techniciansUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeTechniciansUpdated(Technician $techniciansUpdated): static
    {
        if ($this->techniciansUpdated->removeElement($techniciansUpdated)) {
            // set the owning side to null (unless already changed)
            if ($techniciansUpdated->getUpdatedBy() === $this) {
                $techniciansUpdated->setUpdatedBy(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->email;
    }

    /**
     * @return Collection<int, TechnicianFormations>
     */
    public function getTechnicianFormationsUpdated(): Collection
    {
        return $this->technicianFormationsUpdated;
    }

    public function addTechnicianFormationsUpdated(TechnicianFormations $technicianFormationsUpdated): static
    {
        if (!$this->technicianFormationsUpdated->contains($technicianFormationsUpdated)) {
            $this->technicianFormationsUpdated->add($technicianFormationsUpdated);
            $technicianFormationsUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeTechnicianFormationsUpdated(TechnicianFormations $technicianFormationsUpdated): static
    {
        if ($this->technicianFormationsUpdated->removeElement($technicianFormationsUpdated)) {
            // set the owning side to null (unless already changed)
            if ($technicianFormationsUpdated->getUpdatedBy() === $this) {
                $technicianFormationsUpdated->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TechnicianFonction>
     */
    public function getTechnicianFonctionsUpdated(): Collection
    {
        return $this->technicianFonctionsUpdated;
    }

    public function addTechnicianFonctionsUpdated(TechnicianFonction $technicianFonctionsUpdated): static
    {
        if (!$this->technicianFonctionsUpdated->contains($technicianFonctionsUpdated)) {
            $this->technicianFonctionsUpdated->add($technicianFonctionsUpdated);
            $technicianFonctionsUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeTechnicianFonctionsUpdated(TechnicianFonction $technicianFonctionsUpdated): static
    {
        if ($this->technicianFonctionsUpdated->removeElement($technicianFonctionsUpdated)) {
            // set the owning side to null (unless already changed)
            if ($technicianFonctionsUpdated->getUpdatedBy() === $this) {
                $technicianFonctionsUpdated->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TelematicArea>
     */
    public function getTelematicAreaUpdated(): Collection
    {
        return $this->telematicAreaUpdated;
    }

    public function addTelematicAreaUpdated(TelematicArea $telematicAreaUpdated): static
    {
        if (!$this->telematicAreaUpdated->contains($telematicAreaUpdated)) {
            $this->telematicAreaUpdated->add($telematicAreaUpdated);
            $telematicAreaUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeTelematicAreaUpdated(TelematicArea $telematicAreaUpdated): static
    {
        if ($this->telematicAreaUpdated->removeElement($telematicAreaUpdated)) {
            // set the owning side to null (unless already changed)
            if ($telematicAreaUpdated->getUpdatedBy() === $this) {
                $telematicAreaUpdated->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TechnicianVehicle>
     */
    public function getTechnicianVehiclesUpdated(): Collection
    {
        return $this->technicianVehiclesUpdated;
    }

    public function addTechnicianVehiclesUpdated(TechnicianVehicle $technicianVehiclesUpdated): static
    {
        if (!$this->technicianVehiclesUpdated->contains($technicianVehiclesUpdated)) {
            $this->technicianVehiclesUpdated->add($technicianVehiclesUpdated);
            $technicianVehiclesUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeTechnicianVehiclesUpdated(TechnicianVehicle $technicianVehiclesUpdated): static
    {
        if ($this->technicianVehiclesUpdated->removeElement($technicianVehiclesUpdated)) {
            // set the owning side to null (unless already changed)
            if ($technicianVehiclesUpdated->getUpdatedBy() === $this) {
                $technicianVehiclesUpdated->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApiLog>
     */
    public function getApiLogs(): Collection
    {
        return $this->apiLogs;
    }

    public function addApiLog(ApiLog $apiLog): static
    {
        if (!$this->apiLogs->contains($apiLog)) {
            $this->apiLogs->add($apiLog);
            $apiLog->setUser($this);
        }

        return $this;
    }

    public function removeApiLog(ApiLog $apiLog): static
    {
        if ($this->apiLogs->removeElement($apiLog)) {
            // set the owning side to null (unless already changed)
            if ($apiLog->getUser() === $this) {
                $apiLog->setUser(null);
            }
        }

        return $this;
    }
}
