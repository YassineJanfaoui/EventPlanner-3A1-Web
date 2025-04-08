<?php

namespace App\Entity;

use App\Repository\BillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillRepository::class)]
class Bill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'billid')]
    private ?int $billid = null;


    #[ORM\Column]
    private ?int $Amount = null;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?int $Archived = null;

    #[ORM\Column(name: 'eventId')]
    private ?int $EventId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, name: 'dueDate')]
    private ?\DateTimeInterface $DueDate = null;

    #[ORM\Column(type: 'string', length: 255, name: 'paymentStatus')]
    private ?string $PaymentStatus = null;


    public function getBillid(): ?int
    {
        return $this->billid;
    }

    public function setBillid(int $billid): static
    {
        $this->billid = $billid;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->Amount;
    }

    public function setAmount(int $Amount): static
    {
        $this->Amount = $Amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getArchived(): ?int
    {
        return $this->Archived;
    }

    public function setArchived(int $Archived): static
    {
        $this->Archived = $Archived;

        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->EventId;
    }

    public function setEventId(int $EventId): static
    {
        $this->EventId = $EventId;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->DueDate;
    }

    public function setDueDate(\DateTimeInterface $DueDate): static
    {
        $this->DueDate = $DueDate;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->PaymentStatus;
    }

    public function setPaymentStatus(string $PaymentStatus): static
    {
        $this->PaymentStatus = $PaymentStatus;

        return $this;
    }
}
