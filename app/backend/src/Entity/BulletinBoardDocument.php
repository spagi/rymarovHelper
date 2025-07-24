<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\BulletinBoardDocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['document:read']]),
        new Get(normalizationContext: ['groups' => ['document:read']]),
    ]
)]
#[ORM\Entity(repositoryClass: BulletinBoardDocumentRepository::class)]
class BulletinBoardDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['item:read', 'document:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['item:read', 'document:read'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 512)]
    #[Groups(['item:read', 'document:read'])]
    private ?string $fileUrl = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BulletinBoardItem $item = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(string $fileUrl): static
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    public function getItem(): ?BulletinBoardItem
    {
        return $this->item;
    }

    public function setItem(?BulletinBoardItem $item): static
    {
        $this->item = $item;

        return $this;
    }
}
