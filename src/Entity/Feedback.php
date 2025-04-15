<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\FeedbackRepository;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
#[ORM\Table(name: 'feedback')]
class Feedback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'feedbackId',type: 'integer')]
    private ?int $feedbackId = null;

    public function getFeedbackId(): ?int
    {
        return $this->feedbackId;
    }

    public function setFeedbackId(int $feedbackId): self
    {
        $this->feedbackId = $feedbackId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $comment = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $rate = null;

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: 'teamId', referencedColumnName: 'id')]
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

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'feedbacks')]
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
