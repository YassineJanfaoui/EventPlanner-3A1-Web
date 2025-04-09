<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CateringRepository;

#[ORM\Entity(repositoryClass: CateringRepository::class)]
#[ORM\Table(name: 'catering')]
class Catering
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'CateringId', nullable: false)]
    private ?int $CateringId = null;

    public function getCateringId(): ?int
    {
        return $this->CateringId;
    }

    public function setCateringId(int $CateringId): self
    {
        $this->CateringId = $CateringId;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false, name: 'MenuType')]
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

    #[ORM\Column(type: 'integer', nullable: false, name: 'NbrPlates')]
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

    #[ORM\Column(type: 'decimal', nullable: true)]
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

    #[ORM\Column(type: 'string', nullable: true, name: 'MealSchedule')]
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
