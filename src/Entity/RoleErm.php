<?php

namespace App\Entity;

use App\Repository\RoleErmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleErmRepository::class)]
class RoleErm
{
    public const DO = 'DO';
    public const DOA_VI = 'DOA VI';
    public const DOA_VL = 'DOA VL';
    public const DR = 'DR';
    public const AO = 'AO';
    public const RZ = 'RZ';
    public const RAVL = 'RAVL';
    public const RCS = 'RCS';
    public const RCS_MULTI_SITE = 'RCS MULTI SITE';
    public const TM = 'TM';
    public const TECHNICIEN_TELEMATIQUE = 'TECHNICIEN TELEMATIQUE';
    public const CT = 'CT';
    public const RCGO_VI = 'RCGO VI';
    public const RCGO_VL = 'RCGO VL';

    public const ALL = [
        self::DO, self::DOA_VI, self::DOA_VL, self::DR, self::AO, self::RZ, self::RAVL,
        self::RCS, self::RCS_MULTI_SITE, self::TM, self::TECHNICIEN_TELEMATIQUE, self::CT,
        self::RCGO_VI, self::RCGO_VL,
    ];

    /** Rôles considérés "manager" (pas de centre fixe) - utilisé pour filtrer la vue admin dédiée. */
    public const MANAGER_ROLES = [
        self::DO, self::DOA_VI, self::DOA_VL, self::DR, self::AO, self::RZ, self::RAVL,
        self::RCS, self::RCS_MULTI_SITE, self::RCGO_VI, self::RCGO_VL,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\ManyToMany(targetEntity: Person::class, mappedBy: 'roles')]
    private Collection $people;

    public function __construct()
    {
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

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
            $person->addRole($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->people->removeElement($person)) {
            $person->removeRole($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
