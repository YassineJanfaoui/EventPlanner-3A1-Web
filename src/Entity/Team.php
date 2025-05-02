<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TeamRepository;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: 'team')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'teamid')]
    private ?int $teamid = null;
    
    public function getId(): ?int
    {
        return $this->teamid;
    }
    public function setTeamid(int $teamid): self
    {
        $this->teamid = $teamid;
        return $this;
    }

    #[ORM\Column(name: 'TeamName', type: 'string', nullable: false)]
    private ?string $TeamName = null;

    public function getTeamName(): ?string
    {
        return $this->TeamName;
    }

    public function setTeamName(string $TeamName): self
    {
        $this->TeamName = $TeamName;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $Score = null;

    public function getScore(): ?int
    {
        return $this->Score;
    }

    public function setScore(int $Score): self
    {
        $this->Score = $Score;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $Rank = null;

    public function getRank(): ?int
    {
        return $this->Rank;
    }

    public function setRank(int $Rank): self
    {
        $this->Rank = $Rank;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')]
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

    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'team')]
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

    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'team')]
    private Collection $participants;

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        if (!$this->participants instanceof Collection) {
            $this->participants = new ArrayCollection();
        }
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->getParticipants()->contains($participant)) {
            $this->getParticipants()->add($participant);
        }
        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        $this->getParticipants()->removeElement($participant);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Projectsubmission::class, mappedBy: 'team')]
    private Collection $projectsubmissions;

    /**
     * @return Collection<int, Projectsubmission>
     */
    public function getProjectsubmissions(): Collection
    {
        if (!$this->projectsubmissions instanceof Collection) {
            $this->projectsubmissions = new ArrayCollection();
        }
        return $this->projectsubmissions;
    }

    public function addProjectsubmission(Projectsubmission $projectsubmission): self
    {
        if (!$this->getProjectsubmissions()->contains($projectsubmission)) {
            $this->getProjectsubmissions()->add($projectsubmission);
        }
        return $this;
    }

    public function removeProjectsubmission(Projectsubmission $projectsubmission): self
    {
        $this->getProjectsubmissions()->removeElement($projectsubmission);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'teams')]
    #[ORM\JoinTable(
        name: 'eventteam',
        joinColumns: [
            new ORM\JoinColumn(name: 'teamid', referencedColumnName: 'teamid')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')
        ]
    )]
    private Collection $events;

    public function __construct()
    {
        $this->feedbacks = new ArrayCollection();
        $this->participants = new ArrayCollection();
        $this->projectsubmissions = new ArrayCollection();
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

}
