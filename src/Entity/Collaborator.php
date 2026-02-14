<?php

namespace App\Entity;

use App\Repository\CollaboratorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CollaboratorRepository::class)]
#[ORM\HasLifecycleCallbacks] // Permet l'auto-mise à jour de updatedAt
class Collaborator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collaborators')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(options: ["unsigned" => true, "default" => 0])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $vacationInitial = 0;

    #[ORM\Column(options: ["unsigned" => true, "default" => 0])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $rttInitial = 0;

    #[ORM\Column(options: ["unsigned" => true, "default" => 0])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $seniorityLeaveInitial = 0;

    #[ORM\Column(options: ["unsigned" => true, "default" => 0])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $recoveryBalanceInitial = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'collaborator', targetEntity: CollaboratorAbsence::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $absences;

    public int $saturdaysTaken = 0;
    
    public function __construct()
    {
        // Initialisation par défaut pour éviter les erreurs de calcul
        $this->vacationInitial = 0;
        $this->rttInitial = 0;
        $this->seniorityLeaveInitial = 0;
        $this->recoveryBalanceInitial = 0;
        $this->updatedAt = new \DateTimeImmutable();
        $this->absences = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
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

    public function getVacationInitial(): ?int
    {
        return $this->vacationInitial;
    }

    public function setVacationInitial(int $vacationInitial): static
    {
        $this->vacationInitial = $vacationInitial;
        return $this;
    }

    public function getRttInitial(): ?int
    {
        return $this->rttInitial;
    }

    public function setRttInitial(int $rttInitial): static
    {
        $this->rttInitial = $rttInitial;
        return $this;
    }

    public function getSeniorityLeaveInitial(): ?int
    {
        return $this->seniorityLeaveInitial;
    }

    public function setSeniorityLeaveInitial(int $seniorityLeaveInitial): static
    {
        $this->seniorityLeaveInitial = $seniorityLeaveInitial;
        return $this;
    }

    public function getRecoveryBalanceInitial(): ?int
    {
        return $this->recoveryBalanceInitial;
    }

    public function setRecoveryBalanceInitial(int $recoveryBalanceInitial): static
    {
        $this->recoveryBalanceInitial = $recoveryBalanceInitial;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Helper pour afficher le nom complet
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getAbsences(): Collection
    {
        return $this->absences;
    }
}