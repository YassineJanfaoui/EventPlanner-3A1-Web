<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'eventVenueId',type: 'integer')]
    private ?int $eventVenueId = null;

    public function getEventVenueId(): ?int
    {
        return $this->eventVenueId;
    }

    public function setEventVenueId(int $eventVenueId): self
    {
        $this->eventVenueId = $eventVenueId;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Venue::class, inversedBy: 'reservation')]
    #[ORM\JoinColumn(name: 'venueId', referencedColumnName: 'VenueId', unique: true)]
    private ?Venue $venue = null;

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Event::class, inversedBy: 'reservation')]
    #[ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId', unique: true)]
    private ?Event $event = null;

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    #[ORM\Column(name: 'reservationDate',type: 'string', nullable: true)]
    private ?string $reservationDate = null;

    public function getReservationDate(): ?string
    {
        return $this->reservationDate;
    }

    public function setReservationDate(?string $reservationDate): self
    {
        $this->reservationDate = $reservationDate;
        return $this;
    }

    #[ORM\Column(name: 'reservationPrice',type: 'string', nullable: false)]
    private ?string $reservationPrice = null;

    public function getReservationPrice(): ?string
    {
        return $this->reservationPrice;
    }

    public function setReservationPrice(string $reservationPrice): self
    {
        $this->reservationPrice = $reservationPrice;
        return $this;
    }

}
