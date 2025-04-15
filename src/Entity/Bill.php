<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\BillRepository;

#[ORM\Entity(repositoryClass: BillRepository::class)]
#[ORM\Table(name: 'bill')]
class Bill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'billid')]
    private ?int $billid = null;

    #[ORM\Column(type: 'date', nullable: false, name: 'DueDate')]
    #[Assert\NotBlank(message: "Due date is required.")]
    #[Assert\Type("\DateTimeInterface", message: "Due date must be a valid date.")]
    #[Assert\GreaterThanOrEqual(
        value: "today",
        message: "Due date must be today or in the future."
    )]
    private ?\DateTimeInterface $DueDate = null;

    #[ORM\Column(type: 'string', nullable: false, name: 'PaymentStatus')]
    #[Assert\NotBlank(message: "Payment status is required.")]
    #[Assert\Choice(
        choices: ['pending', 'paid'],
        message: "Invalid payment status."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Payment status cannot be longer than {{ limit }} characters."
    )]
    private ?string $PaymentStatus = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "Amount is required.")]
    #[Assert\Positive(message: "Amount must be a positive number.")]
    #[Assert\LessThanOrEqual(
        value: 1000000,
        message: "Amount cannot exceed {{ value }}."
    )]
    private ?int $Amount = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 500,
        minMessage: "Description must be at least {{ limit }} characters long.",
        maxMessage: "Description cannot be longer than {{ limit }} characters."
    )]
    private ?string $Description = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: "Archived status is required.")]
    #[Assert\Choice(
        choices: [0, 1],
        message: "Archived status must be either 0 (false) or 1 (true)."
    )]
    private ?int $Archived = null;

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

    public function getbillid(): ?int
    {
        return $this->billid;
    }

    public function setbillid(int $billid): self
    {
        $this->billid = $billid;
        return $this;
    }


    public function getArchived(): ?int
    {
        return $this->Archived;
    }

    public function setArchived(int $Archived): self
    {
        $this->Archived = $Archived;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;
        return $this;
    }


    public function getAmount(): ?int
    {
        return $this->Amount;
    }

    public function setAmount(int $Amount): self
    {
        $this->Amount = $Amount;
        return $this;
    }


    public function getPaymentStatus(): ?string
    {
        return $this->PaymentStatus;
    }

    public function setPaymentStatus(string $PaymentStatus): self
    {
        $this->PaymentStatus = $PaymentStatus;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->DueDate;
    }

    public function setDueDate(\DateTimeInterface $DueDate): self
    {
        $this->DueDate = $DueDate;
        return $this;
    }
}
