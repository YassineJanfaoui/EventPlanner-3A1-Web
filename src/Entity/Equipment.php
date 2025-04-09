<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'EquipmentId')]
    private ?int $EquipmentId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Name is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Name cannot be longer than {{ limit }} characters."
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "State is required.")]
    private ?string $state = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Category is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Category cannot be longer than {{ limit }} characters."
    )]
    private ?string $category = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Quantity must be provided.")]
    #[Assert\PositiveOrZero(message: "Quantity must be a positive number or zero.")]
    private ?int $quantity = null;

    public function getEquipmentId(): ?int
    {
        return $this->EquipmentId;
    }

    public function setEquipmentId(int $EquipmentId): static
    {
        $this->EquipmentId = $EquipmentId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
