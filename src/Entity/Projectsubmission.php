<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProjectsubmissionRepository;

#[ORM\Entity(repositoryClass: ProjectsubmissionRepository::class)]
#[ORM\Table(name: 'projectsubmission')]
class Projectsubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'submissionId')]
    private ?int $submissionId = null;

    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    public function setSubmissionId(int $submissionId): self
    {
        $this->submissionId = $submissionId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
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

    #[ORM\Column(type: 'string', nullable: false, name: 'FileLink')]
    private ?string $FileLink = null;

    public function getFileLink(): ?string
    {
        return $this->FileLink;
    }

    public function setFileLink(string $FileLink): self
    {
        $this->FileLink = $FileLink;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false, name: 'SubmissionDate')]
    private ?\DateTimeInterface $SubmissionDate = null;

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->SubmissionDate;
    }

    public function setSubmissionDate(\DateTimeInterface $SubmissionDate): self
    {
        $this->SubmissionDate = $SubmissionDate;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'projectsubmissions')]
    #[ORM\JoinColumn(name: 'teamId', referencedColumnName: 'teamid')]
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
}
