<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\FeedbackRepository;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: "Comment cannot be empty")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "Comment must be at least {{ limit }} characters long",
        maxMessage: "Comment cannot be longer than {{ limit }} characters"
    )]
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
    #[Assert\NotBlank(message: "Rating is required")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "Rating must be between {{ min }} and {{ max }} stars"
    )]
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

    #[Assert\NotNull(message: "Team must be selected")]
    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'feedbacks')]
    #[ORM\JoinColumn(name: 'teamId', referencedColumnName: 'teamid', onDelete: 'CASCADE')]  // Update this line
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

    #[Assert\NotNull(message: "Event must be selected")]
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
