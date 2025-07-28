<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\BulletinBoardItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['item:read']]),
        new Get(normalizationContext: ['groups' => ['item:read']]),
    ]
)]
#[ORM\Entity(repositoryClass: BulletinBoardItemRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['title', 'full_text_content'], name: 'fulltext_idx', flags: ['fulltext'])]
class BulletinBoardItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['item:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['item:read'])]
    private ?string $iri = null;

    #[ORM\Column(length: 255)]
    #[Groups(['item:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['item:read'])]
    private ?string $department = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['item:read'])]
    private ?string $agenda = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceNumber = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['item:read'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['item:read'])]
    private ?\DateTimeImmutable $relevantUntil = null;

    #[ORM\Column(length: 255)]
    #[Groups(['item:read'])]
    private ?string $detailUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fullTextContent = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: BulletinBoardDocument::class, mappedBy: 'item', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['item:read'])]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIri(): ?string
    {
        return $this->iri;
    }

    public function setIri(string $iri): static
    {
        $this->iri = $iri;

        return $this;
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

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getAgenda(): ?string
    {
        return $this->agenda;
    }

    public function setAgenda(?string $agenda): static
    {
        $this->agenda = $agenda;

        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?string $referenceNumber): static
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getRelevantUntil(): ?\DateTimeImmutable
    {
        return $this->relevantUntil;
    }

    public function setRelevantUntil(?\DateTimeImmutable $relevantUntil): static
    {
        $this->relevantUntil = $relevantUntil;

        return $this;
    }

    public function getDetailUrl(): ?string
    {
        return $this->detailUrl;
    }

    public function setDetailUrl(string $detailUrl): static
    {
        $this->detailUrl = $detailUrl;

        return $this;
    }

    public function getFullTextContent(): ?string
    {
        return $this->fullTextContent;
    }

    public function setFullTextContent(?string $fullTextContent): static
    {
        $this->fullTextContent = $fullTextContent;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, BulletinBoardDocument>|
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(BulletinBoardDocument $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setItem($this);
        }

        return $this;
    }

    public function removeDocument(BulletinBoardDocument $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getItem() === $this) {
                $document->setItem(null);
            }
        }

        return $this;
    }
}
