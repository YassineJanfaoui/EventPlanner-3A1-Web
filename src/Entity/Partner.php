<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\PartnerRepository;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
#[ORM\Table(name: 'partner')]
class Partner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'partnerId', type: 'integer')]
    private ?int $partnerId = null;

    public function getPartnerId(): ?int
    {
        return $this->partnerId;
    }

    public function setPartnerId(int $partnerId): self
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Partner name cannot be blank")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Partner name must be at least {{ limit }} characters long",
        maxMessage: "Partner name cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z]+$/",
        message: "Partner name can only contain letters"
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Category cannot be blank")]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "Category must be at least {{ limit }} characters long",
        maxMessage: "Category cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s\-\.]+$/",
        message: "Category can only contain letters"
    )]

    private ?string $category = null;

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Workshop::class, mappedBy: 'partner')]
    private Collection $workshops;

    /**
     * @return Collection<int, Workshop>
     */
    public function getWorkshops(): Collection
    {
        if (!$this->workshops instanceof Collection) {
            $this->workshops = new ArrayCollection();
        }
        return $this->workshops;
    }

    public function addWorkshop(Workshop $workshop): self
    {
        if (!$this->getWorkshops()->contains($workshop)) {
            $this->getWorkshops()->add($workshop);
        }
        return $this;
    }

    public function removeWorkshop(Workshop $workshop): self
    {
        $this->getWorkshops()->removeElement($workshop);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Event::class, inversedBy: 'partners')]
    #[ORM\JoinTable(
        name: 'partnership',
        joinColumns: [
            new ORM\JoinColumn(name: 'partnerId', referencedColumnName: 'partnerId')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'eventId', referencedColumnName: 'eventId')
        ]
    )]
    private Collection $events;

    public function __construct()
    {
        $this->workshops = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
