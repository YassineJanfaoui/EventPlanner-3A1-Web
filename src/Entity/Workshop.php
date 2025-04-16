<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\WorkshopRepository;

#[ORM\Entity(repositoryClass: WorkshopRepository::class)]
#[ORM\Table(name: 'workshop')]
class Workshop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'workshopId', type: 'integer')]
    private ?int $workshopId = null;

    public function getWorkshopId(): ?int
    {
        return $this->workshopId;
    }

    public function setWorkshopId(int $workshopId): self
    {
        $this->workshopId = $workshopId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z]+$/",
        message: "title can only contain letters"
    )]
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
    #[Assert\NotBlank(message: "Cannot be blank")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z]+$/",
        message: "Coach name can only contain letters"
    )]
    private ?string $coach = null;

    public function getCoach(): ?string
    {
        return $this->coach;
    }

    public function setCoach(?string $coach): self
    {
        $this->coach = $coach;
        return $this;
    }

    #[ORM\Column(name: 'startDate', type: 'date', nullable: false)]
    #[Assert\Type("\DateTimeInterface", message: "Start date must be a valid date")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "Start date must be today or in the future"
    )]
    private ?\DateTimeInterface $startDate = null;

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "Cannot be blank")]
    private ?int $duration = null;

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "Cannot be blank")]
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

    #[ORM\ManyToOne(targetEntity: Partner::class, inversedBy: 'workshops')]
    #[ORM\JoinColumn(name: 'partnerId', referencedColumnName: 'partnerId')]
    #[Assert\NotBlank(message: "Cannot be blank")]
    private ?Partner $partner = null;

    public function getPartner(): ?Partner
    {
        return $this->partner;
    }

    public function setPartner(?Partner $partner): self
    {
        $this->partner = $partner;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Event::class)]
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
}
