<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\EventRepository;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'eventId', type: 'integer')]
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

/*    #[ORM\OneToMany(targetEntity: Bill::class, mappedBy: 'event')]
    private Collection $bills;

    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'event')]
    private Collection $feedbacks;
*/
    #[ORM\ManyToMany(targetEntity: Venue::class, inversedBy: 'events')]
    #[ORM\JoinTable(name: 'reservation')]
    private Collection $venues;

    #[ORM\OneToOne(targetEntity: Reservation::class, mappedBy: 'event')]
    private ?Reservation $reservation = null;

  /*  #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'events')]
    #[ORM\JoinTable(
        name: 'event_team',
        joinColumns: [new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'TeamId', referencedColumnName: 'TeamId')]
    )]
    private Collection $teams;

    #[ORM\ManyToMany(targetEntity: Equipment::class, inversedBy: 'events')]
    #[ORM\JoinTable(
        name: 'event_equipment',
        joinColumns: [new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'equipment_id', referencedColumnName: 'equipment_id')]
    )]
    private Collection $equipments;

    #[ORM\ManyToMany(targetEntity: Partner::class, inversedBy: 'events')]
    #[ORM\JoinTable(
        name: 'partnership',
        joinColumns: [new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'partner_id', referencedColumnName: 'partner_id')]
    )]
    private Collection $partners;
*/
    public function __construct()
    {
        $this->bills = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->venues = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->equipments = new ArrayCollection();
        $this->partners = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
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
/*
    public function getBills(): Collection
    {
        return $this->bills;
    }

    public function addBill(Bill $bill): self
    {
        if (!$this->bills->contains($bill)) {
            $this->bills->add($bill);
            $bill->setEvent($this);
        }
        return $this;
    }

    public function removeBill(Bill $bill): self
    {
        if ($this->bills->removeElement($bill)) {
            if ($bill->getEvent() === $this) {
                $bill->setEvent(null);
            }
        }
        return $this;
    }

    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setEvent($this);
        }
        return $this;
    }

    public function removeFeedback(Feedback $feedback): self
    {
        if ($this->feedbacks->removeElement($feedback)) {
            if ($feedback->getEvent() === $this) {
                $feedback->setEvent(null);
            }
        }
        return $this;
    }

    public function getVenues(): Collection
    {
        return $this->venues;
    }

    public function addVenue(Venue $venue): self
    {
        if (!$this->venues->contains($venue)) {
            $this->venues->add($venue);
        }
        return $this;
    }

    public function removeVenue(Venue $venue): self
    {
        $this->venues->removeElement($venue);
        return $this;
    }
*/
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
/*
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
        }
        return $this;
    }

    public function removeTeam(Team $team): self
    {
        $this->teams->removeElement($team);
        return $this;
    }

    public function getEquipments(): Collection
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): self
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments->add($equipment);
        }
        return $this;
    }

    public function removeEquipment(Equipment $equipment): self
    {
        $this->equipments->removeElement($equipment);
        return $this;
    }

    public function getPartners(): Collection
    {
        return $this->partners;
    }

    public function addPartner(Partner $partner): self
    {
        if (!$this->partners->contains($partner)) {
            $this->partners->add($partner);
        }
        return $this;
    }

    public function removePartner(Partner $partner): self
    {
        $this->partners->removeElement($partner);
        return $this;
    }*/
}