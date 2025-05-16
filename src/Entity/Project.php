<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Enum\Status;
use App\Enum\Difficulty;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ApiResource]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['project:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['project:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['project:read'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['project:read'])]
    private ?\DateTimeImmutable $started_at = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['project:read'])]
    private ?\DateTimeImmutable $finished_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['project:read'])]
    private ?string $imageUrl = null;

    /**
     * @var Collection<int, ProjectImage>
     */
    #[ORM\OneToMany(targetEntity: ProjectImage::class, mappedBy: 'project', orphanRemoval: true)]
    #[Groups(['project:read'])]
    private Collection $ProjectImage;

    #[ORM\Column(enumType: Status::class)]
    #[Groups(['project:read'])]
    private ?Status $Status = null;

    #[ORM\Column(enumType: Difficulty::class)]
    #[Groups(['project:read'])]
    private ?Difficulty $Difficulty = null;

    public function __construct()
    {
        $this->ProjectImage = new ArrayCollection();
        $this->Status = Status::NOT_STARTED;
        $this->Difficulty = Difficulty::BEGINNER;
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->started_at;
    }

    public function setStartedAt(\DateTimeImmutable $started_at): static
    {
        $this->started_at = $started_at;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?\DateTimeImmutable $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return Collection<int, ProjectImage>
     */
    public function getProjectImage(): Collection
    {
        return $this->ProjectImage;
    }

    public function addProjectImage(ProjectImage $projectImage): static
    {
        if (!$this->ProjectImage->contains($projectImage)) {
            $this->ProjectImage->add($projectImage);
            $projectImage->setProject($this);
        }

        return $this;
    }

    public function removeProjectImage(ProjectImage $projectImage): static
    {
        if ($this->ProjectImage->removeElement($projectImage)) {
            // set the owning side to null (unless already changed)
            if ($projectImage->getProject() === $this) {
                $projectImage->setProject(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->Status;
    }

    public function setStatus(Status $Status): static
    {
        $this->Status = $Status;

        return $this;
    }

    public function getDifficulty(): ?Difficulty
    {
        return $this->Difficulty;
    }

    public function setDifficulty(Difficulty $Difficulty): static
    {
        $this->Difficulty = $Difficulty;

        return $this;
    }
}
