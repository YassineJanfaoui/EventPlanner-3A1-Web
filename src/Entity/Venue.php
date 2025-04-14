<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\VenueRepository;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
#[ORM\Table(name: 'venue')]
class Venue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'VenueId' , type: 'integer')]
    private ?int $VenueId = null;

    public const AVAILABILITY_CHOICES = [
        '8h-20h' => '8h-20h',
        '8h-13h' => '8h-13h',
        '15h-20h' => '15h-20h',
    ];

    public const PARKING_CHOICES = [
        'Available' => 'Available',
        'Unavailable' => 'Unavailable',
    ];
    
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

    #[ORM\Column(name: 'NbrPlaces',type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0, message: "The number of places must be zero or more.")]
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

    #[ORM\Column(name: 'VenueName',type: 'string', nullable: false)]
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
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0, message: "Cost must be zero or more.")]
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
