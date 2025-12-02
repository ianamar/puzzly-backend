<?php
// src/Entity/Puzzle.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PuzzleRepository;
use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "puzzles")]
class Puzzle
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "uploaded_by_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $uploadedBy = null;

    #[ORM\Id, ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    private string $name;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type:"string", length:255)]
    private string $imagePath;

    #[ORM\Column(type:"integer")]
    private int $numRows = 3;

    #[ORM\Column(type:"integer")]
    private int $numCols = 3;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    //  Getters y Setters 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

     public function getCategory(): ?string
    {
        return $this->category;
    }
    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }
    
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function getImageUrl(string $baseUrl): string
{
    $p = ltrim((string)$this->imagePath, '/');
    return rtrim($baseUrl, '/') . '/' . $p;
}
    public function setImagePath(string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getNumRows(): int
    {
        return $this->numRows;
    }

    public function setNumRows(int $numRows): self
    {
        $this->numRows = $numRows;
        return $this;
    }

    public function getNumCols(): int
    {
        return $this->numCols;
    }

    public function setNumCols(int $numCols): self
    {
        $this->numCols = $numCols;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

      public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): self
    {
        $this->uploadedBy = $uploadedBy;
        return $this;
    }
}
