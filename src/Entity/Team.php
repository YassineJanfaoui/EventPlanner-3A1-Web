<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: "team")]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "TeamName", length: 255, nullable: true)]
    private ?string $TeamName = null;

    #[ORM\Column(name: "Score", nullable: true)]
    private ?float $Score = null;

    #[ORM\Column(name: "Rank", nullable: true)]
    private ?int $Rank = null;

    #[ORM\Column(name: "eventId", nullable: true)]
    private ?int $eventId = null;

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

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(?int $eventId): self
    {
        $this->eventId = $eventId;
        return $this;
    }
}
