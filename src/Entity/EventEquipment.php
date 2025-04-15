<?php

namespace App\Entity;

use App\Repository\EventEquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventEquipmentRepository::class)]
#[ORM\Table(name: 'eventequipment')]
class EventEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'eventEquipment')]
    #[ORM\JoinColumn(name: 'EventId', referencedColumnName: 'eventId', nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: Equipment::class, inversedBy: 'eventEquipment')]
    #[ORM\JoinColumn(name: 'EquipmentId', referencedColumnName: 'EquipmentId', nullable: false, onDelete: 'CASCADE')]
    private ?Equipment $equipment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, name: 'startDate')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, name: 'endDate')]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "startDate",
        message: "The end date must be equal to or after the start date"
    )]
    private ?\DateTimeInterface $endDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;
        return $this;
    }

    public function getEquipment(): ?Equipment
    {
        return $this->equipment;
    }

    public function setEquipment(?Equipment $equipment): static
    {
        $this->equipment = $equipment;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    #[ORM\PrePersist]
    public function setEquipmentUnavailable(): void
    {
        if ($this->equipment) {
            $this->equipment->setState('unavailable');
        }
    }

    #[ORM\PreUpdate]
    public function updateEquipmentStatus(): void
    {
        return;
    }

    #[ORM\PreRemove]
    public function setEquipmentAvailable(): void
    {
        if ($this->equipment) {
            $this->equipment->setState('functional');
        }
    }
}
