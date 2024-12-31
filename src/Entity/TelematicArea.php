<?php

namespace App\Entity;

use App\Repository\TelematicAreaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelematicAreaRepository::class)]
class TelematicArea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'telematicArea', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cgo $cgo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCgo(): ?Cgo
    {
        return $this->cgo;
    }

    public function setCgo(Cgo $cgo): static
    {
        $this->cgo = $cgo;

        return $this;
    }
}
