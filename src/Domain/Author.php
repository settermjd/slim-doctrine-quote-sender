<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity, ORM\Table(name: 'quote_authors')]
class Author
{
    #[
        ORM\Id,
        ORM\Column(name: "author_id", type: 'uuid'),
        ORM\GeneratedValue(strategy: 'CUSTOM'),
        ORM\CustomIdGenerator(class: UuidGenerator::class)
    ]
    private string|null $authorId = null;

    #[ORM\Column(name: "full_name", type: 'string', length: 200, unique: true, nullable: false)]
    private string $fullName;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    #[ORM\OneToMany(targetEntity: Quote::class, mappedBy: 'quote')]
    private Collection $quotes;

    public function __construct(string $fullName)
    {
        $this->createdAt = new DateTimeImmutable('now');

        $this->fullName = $fullName;
        $this->quotes = new ArrayCollection();
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

}