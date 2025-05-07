<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\EventTeamRepository;

#[ORM\Entity(repositoryClass: EventTeamRepository::class)]
#[ORM\Table(name: 'event_team')]
class EventTeam
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "submission_id", type: Types::INTEGER)]
    private ?int $submissionId = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'eventTeams')] // Corrected inversedBy to 'eventTeams'
    #[ORM\JoinColumn(referencedColumnName: 'eventId')] 
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'eventTeams')] // Corrected inversedBy to 'eventTeams'
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'teamid', onDelete: 'CASCADE')] // Updated name to match DB
    private ?Team $team = null;

    #[ORM\Column(name: "title", type: "string", length: 255, nullable: true)] // Added title property
    private ?string $title = null;

    #[ORM\Column(name: "file_link", type: "string", length: 255, nullable: true)]
    private ?string $file_link = null;

    #[ORM\Column(name: "submission_date", type: "date", nullable: true)]
    private ?\DateTimeInterface $submission_date = null;

    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    public function setSubmissionId(?int $submissionId): self
    {
        $this->submissionId = $submissionId;
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

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getFileLink(): ?string
    {
        return $this->file_link;
    }

    public function setFileLink(?string $file_link): self
    {
        $this->file_link = $file_link;
        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submission_date;
    }

    public function setSubmissionDate(?\DateTimeInterface $submission_date): self
    {
        $this->submission_date = $submission_date;
        return $this;
    }

    public function getTitle(): ?string // Added getter for title
    {
        return $this->title;
    }

    public function setTitle(?string $title): self // Added setter for title
    {
        $this->title = $title;
        return $this;
    }
}
