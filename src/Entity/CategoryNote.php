<?php

namespace App\Entity;

use App\Repository\CategoryNoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryNoteRepository::class)]
class CategoryNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: "categoryNotes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: Notes::class, inversedBy: "categoryNotes")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Notes $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getNote(): ?Notes
    {
        return $this->note;
    }

    public function setNote(?Notes $note): static
    {
        $this->note = $note;

        return $this;
    }
}