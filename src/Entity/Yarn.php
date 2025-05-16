<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\FiberContent;
use App\Enum\YarnWeight;
use App\Repository\YarnRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: YarnRepository::class)]
#[ApiResource]
class Yarn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['yarn:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['yarn:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['yarn:read'])]
    private ?string $color = null;

    #[ORM\Column(length: 255)]
    #[Groups(['yarn:read'])]
    private ?string $brand = null;

    #[ORM\Column]
    #[Groups(['yarn:read'])]
    private ?int $quantity = null;

    #[ORM\Column(length: 255)]
    #[Groups(['yarn:read'])]
    private ?string $imageUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['yarn:read'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['yarn:read'])]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column(enumType: FiberContent::class)]
    #[Groups(['yarn:read'])]
    private ?FiberContent $FiberContent = null;

    #[ORM\Column(enumType: YarnWeight::class)]
    #[Groups(['yarn:read'])]
    private ?YarnWeight $Weight = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getFiberContent(): ?FiberContent
    {
        return $this->FiberContent;
    }

    public function setFiberContent(FiberContent $FiberContent): static
    {
        $this->FiberContent = $FiberContent;

        return $this;
    }

    public function getWeight(): ?YarnWeight
    {
        return $this->Weight;
    }

    public function setWeight(YarnWeight $Weight): static
    {
        $this->Weight = $Weight;

        return $this;
    }
}
