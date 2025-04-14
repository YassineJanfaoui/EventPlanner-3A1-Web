<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\BillRepository;

#[ORM\Entity(repositoryClass: BillRepository::class)]
#[ORM\Table(name: 'bill')]
class Bill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $BillId = null;

    public function getBillId(): ?int
    {
        return $this->BillId;
    }

    public function setBillId(int $BillId): self
    {
        $this->BillId = $BillId;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $DueDate = null;

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->DueDate;
    }

    public function setDueDate(\DateTimeInterface $DueDate): self
    {
        $this->DueDate = $DueDate;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $PaymentStatus = null;

    public function getPaymentStatus(): ?string
    {
        return $this->PaymentStatus;
    }

    public function setPaymentStatus(string $PaymentStatus): self
    {
        $this->PaymentStatus = $PaymentStatus;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $Amount = null;

    public function getAmount(): ?int
    {
        return $this->Amount;
    }

    public function setAmount(int $Amount): self
    {
        $this->Amount = $Amount;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $Description = null;

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $Archived = null;

    public function getArchived(): ?int
    {
        return $this->Archived;
    }

    public function setArchived(int $Archived): self
    {
        $this->Archived = $Archived;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'bills')]
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
