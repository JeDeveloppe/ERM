<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CollaboratorAbsenceRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CollaboratorAbsenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CollaboratorAbsence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'absences')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] // AJOUT DE onDelete: 'CASCADE'
    private ?Collaborator $collaborator = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['CP', 'RTT', 'JTT'], message: 'Choisissez un type valide.')]
    private ?string $type = null; // CP, RTT, ou JTT

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\GreaterThanOrEqual('today')]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\Expression(
        "this.getEndDate() >= this.getStartDate()",
        message: "La date de fin ne peut pas être avant la date de début."
    )]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters et Setters ---

    public function getId(): ?int { return $this->id; }

    public function getCollaborator(): ?Collaborator { return $this->collaborator; }
    public function setCollaborator(?Collaborator $collaborator): static { $this->collaborator = $collaborator; return $this; }

    public function getOwner(): ?User { return $this->owner; }
    public function setOwner(?User $owner): static { $this->owner = $owner; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }

    public function getStartDate(): ?\DateTimeImmutable { return $this->startDate; }
    public function setStartDate(\DateTimeImmutable $startDate): static { $this->startDate = $startDate; return $this; }

    public function getEndDate(): ?\DateTimeImmutable { return $this->endDate; }
    public function setEndDate(\DateTimeImmutable $endDate): static { $this->endDate = $endDate; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}