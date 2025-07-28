<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebPageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebPageRepository::class)]
#[ORM\Table(name: 'web_page')]
#[ORM\Index(name: 'web_content_fulltext_idx', columns: ['title', 'content'], flags: ['fulltext'])] class WebPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 512, unique: true)]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, length: 4294967295, nullable: true)] // LONGTEXT
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $crawledAt = null;

    public function __construct()
    {
        $this->crawledAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCrawledAt(): ?\DateTimeImmutable
    {
        return $this->crawledAt;
    }

    public function setCrawledAt(\DateTimeImmutable $crawledAt): static
    {
        $this->crawledAt = $crawledAt;

        return $this;
    }
}
