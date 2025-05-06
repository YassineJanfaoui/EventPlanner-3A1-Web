<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\EquipmentRepository;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[ORM\Table(name: 'equipment')]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'EquipmentId')]
    private ?int $EquipmentId = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Name is required.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Name must be at least {{ limit }} characters long.",
        maxMessage: "Name cannot be longer than {{ limit }} characters."
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\Choice(
        choices: ['functional', 'maintenance', 'unavailable'],
        message: "Invalid state. Must be: functional, maintenance, or unavailable."
    )]
    private ?string $state = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Category is required.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Category must be at least {{ limit }} characters long.",
        maxMessage: "Category cannot be longer than {{ limit }} characters."
    )]
    private ?string $category = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "Quantity is required.")]
    #[Assert\PositiveOrZero(message: "Quantity must be zero or a positive number.")]
    private ?int $quantity = null;

    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'equipments')]
    #[ORM\JoinTable(
        name: 'eventequipment',
        joinColumns: [
            new ORM\JoinColumn(name: 'EquipmentId', referencedColumnName: 'EquipmentId')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'EventId', referencedColumnName: 'eventId')
        ]
    )]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        if (!$this->events instanceof Collection) {
            $this->events = new ArrayCollection();
        }
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
        }
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        $this->getEvents()->removeElement($event);
        return $this;
    }

    public function getEquipmentId(): ?int
    {
        return $this->EquipmentId;
    }

    public function setEquipmentId(int $EquipmentId): self
    {
        $this->EquipmentId = $EquipmentId;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }


    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
