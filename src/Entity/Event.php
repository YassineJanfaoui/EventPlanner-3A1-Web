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
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'eventId')]
    private ?int $eventId = null;

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): self
    {
        $this->eventId = $eventId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true, name: 'startDate')]
    private ?string $startDate = null;

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true, name: 'endDate')]
    private ?string $endDate = null;

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false, name: 'maxParticipants')]
    private ?int $maxParticipants = null;

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): self
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $fee = null;

    public function getFee(): ?int
    {
        return $this->fee;
    }

    public function setFee(int $fee): self
    {
        $this->fee = $fee;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'userid', referencedColumnName: 'userid')]
    private ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $image = null;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Bill::class, mappedBy: 'event')]
    private Collection $bills;

    /**
     * @return Collection<int, Bill>
     */
    public function getBills(): Collection
    {
        if (!$this->bills instanceof Collection) {
            $this->bills = new ArrayCollection();
        }
        return $this->bills;
    }

    public function addBill(Bill $bill): self
    {
        if (!$this->getBills()->contains($bill)) {
            $this->getBills()->add($bill);
        }
        return $this;
    }

    public function removeBill(Bill $bill): self
    {
        $this->getBills()->removeElement($bill);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'event')]
    private Collection $feedbacks;

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedbacks(): Collection
    {
        if (!$this->feedbacks instanceof Collection) {
            $this->feedbacks = new ArrayCollection();
        }
        return $this->feedbacks;
    }

    public function addFeedback(Feedback $feedback): self
    {
        if (!$this->getFeedbacks()->contains($feedback)) {
            $this->getFeedbacks()->add($feedback);
        }
        return $this;
    }

    public function removeFeedback(Feedback $feedback): self
    {
        $this->getFeedbacks()->removeElement($feedback);
        return $this;
    }

    #[ORM\OneToOne(targetEntity: Reservation::class, mappedBy: 'event')]
    private ?Reservation $reservation = null;

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Equipment::class, inversedBy: 'events')]
    #[ORM\JoinTable(
        name: 'eventequipment',
        joinColumns: [
            new ORM\JoinColumn(name: 'EventId', referencedColumnName: 'eventId')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'EquipmentId', referencedColumnName: 'EquipmentId')
        ]
    )]
    private Collection $equipments;

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipments(): Collection
    {
        if (!$this->equipments instanceof Collection) {
            $this->equipments = new ArrayCollection();
        }
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): self
    {
        if (!$this->getEquipments()->contains($equipment)) {
            $this->getEquipments()->add($equipment);
        }
        return $this;
    }

    public function removeEquipment(Equipment $equipment): self
    {
        $this->getEquipments()->removeElement($equipment);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'events')]
    #[ORM\JoinTable(
        name: 'eventteam',
        joinColumns: [
            new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'teamId', referencedColumnName: 'teamid')
        ]
    )]
    private Collection $teams;

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        if (!$this->teams instanceof Collection) {
            $this->teams = new ArrayCollection();
        }
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->getTeams()->contains($team)) {
            $this->getTeams()->add($team);
        }
        return $this;
    }

    public function removeTeam(Team $team): self
    {
        $this->getTeams()->removeElement($team);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Partner::class, inversedBy: 'events')]
    #[ORM\JoinTable(
        name: 'partnership',
        joinColumns: [
            new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'partnerId', referencedColumnName: 'partnerId')
        ]
    )]
    private Collection $partners;

    public function __construct()
    {
        $this->bills = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->equipments = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->partners = new ArrayCollection();
    }

    /**
     * @return Collection<int, Partner>
     */
    public function getPartners(): Collection
    {
        if (!$this->partners instanceof Collection) {
            $this->partners = new ArrayCollection();
        }
        return $this->partners;
    }

    public function addPartner(Partner $partner): self
    {
        if (!$this->getPartners()->contains($partner)) {
            $this->getPartners()->add($partner);
        }
        return $this;
    }

    public function removePartner(Partner $partner): self
    {
        $this->getPartners()->removeElement($partner);
        return $this;
    }
}
