<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use JsonSerializable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface; 

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[HasLifecycleCallbacks]


class User implements JsonSerializable, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $username = null;

    #[ORM\Column(type: "string", length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    // Relation with note One To Many

    #[ORM\OneToMany(targetEntity: Notes::class, mappedBy: 'user', cascade: ['persist'])]
    private $notes;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Notes>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Notes $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setUser($this);
        }

        return $this;
    }

    public function removeNote(Notes $note): self
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getUser() === $this) {
                $note->setUser(null);
            }
        }

        return $this;
    }

    // -------
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function jsonSerialize():mixed
    {
        return [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(), 
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt() 
        ];
    }
}
