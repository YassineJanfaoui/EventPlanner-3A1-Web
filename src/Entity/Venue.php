<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\VenueRepository;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
#[ORM\Table(name: 'venue')]
class Venue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'VenueId')]
    private ?int $VenueId = null;

    public function getVenueId(): ?int
    {
        return $this->VenueId;
    }

    public function setVenueId(int $VenueId): self
    {
        $this->VenueId = $VenueId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $Location = null;

    public function getLocation(): ?string
    {
        return $this->Location;
    }

    public function setLocation(string $Location): self
    {
        $this->Location = $Location;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false, name: 'NbrPlaces')]
    private ?int $NbrPlaces = null;

    public function getNbrPlaces(): ?int
    {
        return $this->NbrPlaces;
    }

    public function setNbrPlaces(int $NbrPlaces): self
    {
        $this->NbrPlaces = $NbrPlaces;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false, name: 'VenueName')]
    private ?string $VenueName = null;

    public function getVenueName(): ?string
    {
        return $this->VenueName;
    }

    public function setVenueName(string $VenueName): self
    {
        $this->VenueName = $VenueName;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $Availability = null;

    public function getAvailability(): ?string
    {
        return $this->Availability;
    }

    public function setAvailability(string $Availability): self
    {
        $this->Availability = $Availability;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $Cost = null;

    public function getCost(): ?float
    {
        return $this->Cost;
    }

    public function setCost(?float $Cost): self
    {
        $this->Cost = $Cost;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $Parking = null;

    public function getParking(): ?string
    {
        return $this->Parking;
    }

    public function setParking(string $Parking): self
    {
        $this->Parking = $Parking;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Catering::class, mappedBy: 'venue')]
    private Collection $caterings;

    /**
     * @return Collection<int, Catering>
     */
    public function getCaterings(): Collection
    {
        if (!$this->caterings instanceof Collection) {
            $this->caterings = new ArrayCollection();
        }
        return $this->caterings;
    }

    public function addCatering(Catering $catering): self
    {
        if (!$this->getCaterings()->contains($catering)) {
            $this->getCaterings()->add($catering);
        }
        return $this;
    }

    public function removeCatering(Catering $catering): self
    {
        $this->getCaterings()->removeElement($catering);
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Reservation::class, mappedBy: 'venue')]
    private ?Reservation $reservation = null;

    public function __construct()
    {
        $this->caterings = new ArrayCollection();
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }
}
