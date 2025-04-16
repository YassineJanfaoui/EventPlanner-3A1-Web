<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\VenueRepository;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
#[ORM\Table(name: 'Venue')]
class Venue
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'VenueId',type: 'integer')]
    private ?int $VenueId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $location = null;

    #[ORM\Column(name: 'nbrPlaces', type: 'integer', nullable: false)]
    private ?int $nbrPlaces = null;

    #[ORM\Column(name: 'venueName', type: 'string', length: 255, nullable: false)]
    private ?string $venueName = null;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private ?string $availability = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $cost = null;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private ?string $parking = null;

    #[ORM\OneToMany(targetEntity: Catering::class, mappedBy: 'venue')]
    private Collection $caterings;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'venue')]
    private Collection $events;

    #[ORM\OneToOne(targetEntity: Reservation::class, mappedBy: 'venue')]
    private ?Reservation $reservation = null;

    public function __construct()
    {
        $this->caterings = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getVenueId(): ?int
    {
        return $this->VenueId;
    }

    public function setVenueId(int $VenueId): self
    {
        $this->VenueId = $VenueId;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getNbrPlaces(): ?int
    {
        return $this->nbrPlaces;
    }

    public function setNbrPlaces(int $nbrPlaces): self
    {
        $this->nbrPlaces = $nbrPlaces;
        return $this;
    }

    public function getVenueName(): ?string
    {
        return $this->venueName;
    }

    public function setVenueName(string $venueName): self
    {
        $this->venueName = $venueName;
        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(string $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;
        return $this;
    }

    public function getParking(): ?string
    {
        return $this->parking;
    }

    public function setParking(string $parking): self
    {
        $this->parking = $parking;
        return $this;
    }

    public function getCaterings(): Collection
    {
        return $this->caterings;
    }

    public function addCatering(Catering $catering): self
    {
        if (!$this->caterings->contains($catering)) {
            $this->caterings->add($catering);
            $catering->setVenue($this);
        }
        return $this;
    }

    public function removeCatering(Catering $catering): self
    {
        if ($this->caterings->removeElement($catering)) {
            if ($catering->getVenue() === $this) {
                $catering->setVenue(null);
            }
        }
        return $this;
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setVenue($this);
        }
        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            if ($event->getVenue() === $this) {
                $event->setVenue(null);
            }
        }
        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        if ($reservation !== null && $reservation->getVenue() !== $this) {
            $reservation->setVenue($this);
        }
        $this->reservation = $reservation;
        return $this;
    }
}