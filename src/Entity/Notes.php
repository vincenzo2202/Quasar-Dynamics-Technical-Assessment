<?php

namespace App\Entity;

use App\Repository\NotesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use JsonSerializable;

#[ORM\Entity(repositoryClass: NotesRepository::class)]
#[HasLifecycleCallbacks]

class Notes implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $note = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "notes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\OneToMany(targetEntity: CategoryNote::class, mappedBy: 'note')]
    private $categoryNotes;

    public function __construct()
    {
        $this->categoryNotes = new ArrayCollection();
    }

    /**
     * @return Collection|CategoryNote[]
     */
    public function getCategoryNotes(): Collection
    {
        return $this->categoryNotes;
    }

    public function addCategoryNote(CategoryNote $categoryNote): self
    {
        if (!$this->categoryNotes->contains($categoryNote)) {
            $this->categoryNotes[] = $categoryNote;
            $categoryNote->setNote($this);
        }

        return $this;
    }

    public function removeCategoryNote(CategoryNote $categoryNote): self
    {
        if ($this->categoryNotes->removeElement($categoryNote)) {
            // set the owning side to null (unless already changed)
            if ($categoryNote->getNote() === $this) {
                $categoryNote->setNote(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->created_at = $this->created_at ?? new \DateTime();
        $this->updated_at = $this->updated_at ?? new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'note' => $this->note,
            'user' => $this->user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}