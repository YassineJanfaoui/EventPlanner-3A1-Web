<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ParticipantRepository;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'participant')]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'participantId', type: 'integer')]
    private ?int $participantId = null;

    public function getParticipantId(): ?int
    {
        return $this->participantId;
    }

    public function setParticipantId(int $participantId): self
    {
        $this->participantId = $participantId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Participant name cannot be blank.")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Participant name must be at least {{ limit }} characters long.",
        maxMessage: "Participant name cannot be longer than {{ limit }} characters."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s]+$/",
        message: "Participant name can only contain letters and spaces."
    )]
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

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Affiliation cannot be longer than {{ limit }} characters."
    )]
    private ?string $affiliation = null;

    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    public function setAffiliation(?string $affiliation): self
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Range(
        min: 1,
        max: 120,
        notInRangeMessage: "Age must be between {{ min }} and {{ max }}."
    )]
    private ?int $age = null;

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'participants')]
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