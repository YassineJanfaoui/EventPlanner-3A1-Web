<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TeamRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: "team")]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name:"teamid",type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: "TeamName", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Team name cannot be empty")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Team name must be at least {{ limit }} characters long",
        maxMessage: "Team name cannot be longer than {{ limit }} characters"
    )]
    private ?string $TeamName = null;

    #[ORM\Column(name: "Score", nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: "Score must be between {{ min }} and {{ max }}"
    )]
    private ?float $Score = null;

    #[ORM\Column(name: "Rank", nullable: true)]
    
    private ?int $Rank = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId', nullable: true)]
    private ?Event $event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTeamName(): ?string
    {
        return $this->TeamName;
    }

    public function setTeamName(?string $TeamName): self
    {
        $this->TeamName = $TeamName;
        return $this;
    }

    public function getScore(): ?float
    {
        return $this->Score;
    }

    public function setScore(?float $Score): self
    {
        $this->Score = $Score;
        return $this;
    }

    public function getRank(): ?int
    {
        return $this->Rank;
    }

    public function setRank(?int $Rank): self
    {
        $this->Rank = $Rank;
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    // Remove getEventId() and setEventId() methods

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: EventTeam::class, cascade: ['persist', 'remove'])]
    private Collection $eventTeams;

    public function __construct()
    {
        // ... other initializations ...
        $this->eventTeams = new ArrayCollection();
    }

    /**
     * @return Collection<int, EventTeam>
     */
    public function getEventTeams(): Collection
    {
        return $this->eventTeams;
    }

    public function addEventTeam(EventTeam $eventTeam): self
    {
        if (!$this->eventTeams->contains($eventTeam)) {
            $this->eventTeams->add($eventTeam);
            $eventTeam->setTeam($this);
        }

        return $this;
    }

    public function removeEventTeam(EventTeam $eventTeam): self
    {
        if ($this->eventTeams->removeElement($eventTeam)) {
            // set the owning side to null (unless already changed)
            if ($eventTeam->getTeam() === $this) {
                $eventTeam->setTeam(null);
            }
        }

        return $this;
    }
}
