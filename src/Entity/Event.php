<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name:"eventId",type: Types::INTEGER)]
    private ?int $eventId = null;

    public function getId(): ?int
    {
        return $this->eventId;
    }

    public function setId(int $eventId): self
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

    #[ORM\Column(name: "startDate",type: 'string', nullable: false)]
    private ?string $startDate = null;

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(string $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    #[ORM\Column(name: "endDate",type: 'string', nullable: true)]
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

    #[ORM\Column(name:'maxParticipants',type: 'integer', nullable: false)]
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

    #[ORM\OneToMany(targetEntity: EventTeam::class, mappedBy: 'event')]
    private Collection $eventTeams;

    /**
     * @return Collection<int, EventTeam>
     */
    public function getEventTeams(): Collection
    {
        if (!$this->eventTeams instanceof Collection) {
            $this->eventTeams = new ArrayCollection();
        }
        return $this->eventTeams;
    }

    public function addEventTeam(EventTeam $eventTeam): self
    {
        if (!$this->getEventTeams()->contains($eventTeam)) {
            $this->getEventTeams()->add($eventTeam);
        }
        return $this;
    }

    public function removeEventTeam(EventTeam $eventTeam): self
    {
        $this->getEventTeams()->removeElement($eventTeam);
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

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'event')]
    private Collection $teams;

    public function __construct()
    {
        $this->eventTeams = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

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

}
