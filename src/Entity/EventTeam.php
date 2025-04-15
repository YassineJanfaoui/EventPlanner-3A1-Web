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
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'submission_id',type: 'integer')]
    private ?int $submission_id = null;

    public function getSubmission_id(): ?int
    {
        return $this->submission_id;
    }

    public function setSubmission_id(int $submission_id): self
    {
        $this->submission_id = $submission_id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'eventTeams')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'eventId', nullable: false)]
    #[Assert\NotNull(message: "Event must be selected")]
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

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'eventTeams')]
    #[ORM\JoinColumn(name: 'team_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Team must be selected")]
    private ?Team $team = null;

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Title cannot be empty")]
    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "File link cannot be empty")]
    #[Assert\Url(message: "The file link '{{ value }}' is not a valid URL")]
    private ?string $file_link = null;

    public function getFile_link(): ?string
    {
        return $this->file_link;
    }

    public function setFile_link(string $file_link): self
    {
        $this->file_link = $file_link;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $submission_date = null;

    public function __construct()
    {
        // Set default submission date to current date
        $this->submission_date = new \DateTime();
    }

    public function getSubmission_date(): ?\DateTimeInterface
    {
        return $this->submission_date;
    }

    public function setSubmission_date(\DateTimeInterface $submission_date): self
    {
        $this->submission_date = $submission_date;
        return $this;
    }

    public function getSubmissionId(): ?int
    {
        return $this->submission_id;
    }

    public function getFileLink(): ?string
    {
        return $this->file_link;
    }

    public function setFileLink(string $file_link): static
    {
        $this->file_link = $file_link;

        return $this;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submission_date;
    }

    public function setSubmissionDate(\DateTimeInterface $submission_date): static
    {
        $this->submission_date = $submission_date;

        return $this;
    }
}
