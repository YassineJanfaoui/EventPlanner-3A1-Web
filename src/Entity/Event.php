<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'eventId')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $eventId = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'startDate', type: 'string', length: 255, nullable: true)]
    private ?string $startDate = null;

    #[ORM\Column(name: 'endDate', type: 'string', length: 255, nullable: true)]
    private ?string $endDate = null;

    #[ORM\Column(name: 'maxParticipants', type: 'integer', nullable: false)]
    private ?int $maxParticipants = null;

    #[ORM\Column(name: 'description', type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'fee', type: 'integer', nullable: false)]
    private ?int $fee = null;

    #[ORM\Column(name: 'image', type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'userid', referencedColumnName: 'userid')]
    private ?User $user = null;


    #[ORM\OneToOne(targetEntity: Reservation::class, mappedBy: 'event')]
    private ?Reservation $reservation = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventTeam::class)]
private Collection $eventTeams;

    public function __construct()
    {
        $this->eventTeams = new ArrayCollection();
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
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

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): self
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getFee(): ?int
    {
        return $this->fee;
    }

    public function setFee(int $fee): self
    {
        $this->fee = $fee;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }


    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        if ($reservation === null && $this->reservation !== null) {
            $this->reservation->setEvent(null);
        }

        if ($reservation !== null && $reservation->getEvent() !== $this) {
            $reservation->setEvent($this);
        }

        $this->reservation = $reservation;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }
    
// Add these methods:
public function getEventTeams(): Collection
{
    return $this->eventTeams;
}

public function addEventTeam(EventTeam $eventTeam): self
{
    if (!$this->eventTeams->contains($eventTeam)) {
        $this->eventTeams->add($eventTeam);
        $eventTeam->setEvent($this);
    }
    return $this;
}

public function removeEventTeam(EventTeam $eventTeam): self
{
    if ($this->eventTeams->removeElement($eventTeam)) {
        if ($eventTeam->getEvent() === $this) {
            $eventTeam->setEvent(null);
        }
    }
    return $this;
}

}
