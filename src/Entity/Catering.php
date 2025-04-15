<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CateringRepository;
use PhpParser\Node\Expr\Cast\Double;

#[ORM\Entity(repositoryClass: CateringRepository::class)]
#[ORM\Table(name: 'catering')]
class Catering
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'CateringId',type: 'integer')]
    private ?int $CateringId = null;

    public const MENU_TYPE_CHOICES = [
        'Normal' => 'Normal',
        'Vegan' => 'Vegan',
        'Gluten Free' => 'Gluten Free',
        'Vegetarian' => 'Vegetarian',
    ];

    public const MEAL_SCHEDULE_CHOICES = [
        'Breakfast' => 'Breakfast',
        'Lunch' => 'Lunch',
        'Dinner' => 'Dinner',
        'Snacks' => 'Snacks',
    ];

    public const BEVERAGE_CHOICES = [
        'Water' => 'Water',
        'Coffee' => 'Coffee',
        'Tea' => 'Tea',
    ];

    public function getCateringId(): ?int
    {
        return $this->CateringId;
    }

    public function setCateringId(int $CateringId): self
    {
        $this->CateringId = $CateringId;
        return $this;
    }

    #[ORM\Column(name: 'MenuType',type: 'string', nullable: false)]
    private ?string $MenuType = null;

    public function getMenuType(): ?string
    {
        return $this->MenuType;
    }

    public function setMenuType(string $MenuType): self
    {
        $this->MenuType = $MenuType;
        return $this;
    }

    #[ORM\Column(name: 'NbrPlates',type: 'integer', nullable: false)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $NbrPlates = null;

    public function getNbrPlates(): ?int
    {
        return $this->NbrPlates;
    }

    public function setNbrPlates(int $NbrPlates): self
    {
        $this->NbrPlates = $NbrPlates;
        return $this;
    }

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?float $Pricing = null;

    public function getPricing(): ?float
    {
        return $this->Pricing;
    }

    public function setPricing(?float $Pricing): self
    {
        $this->Pricing = $Pricing;
        return $this;
    }


    #[ORM\Column(name: 'MealSchedule',type: 'string', nullable: true)]
    private ?string $MealSchedule = null;

    public function getMealSchedule(): ?string
    {
        return $this->MealSchedule;
    }

    public function setMealSchedule(?string $MealSchedule): self
    {
        $this->MealSchedule = $MealSchedule;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $Beverages = null;

    public function getBeverages(): ?string
    {
        return $this->Beverages;
    }

    public function setBeverages(?string $Beverages): self
    {
        $this->Beverages = $Beverages;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Venue::class, inversedBy: 'caterings')]
    #[ORM\JoinColumn(name: 'VenueId', referencedColumnName: 'VenueId')]
    private ?Venue $venue = null;

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;
        return $this;
    }
}
