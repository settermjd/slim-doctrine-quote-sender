<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[Entity, Table(name: 'quotes')]
class Quote
{
    #[
        Id,
        Column(name: "quote_id", type: 'uuid'),
        GeneratedValue(strategy: 'CUSTOM'),
        CustomIdGenerator(class: UuidGenerator::class)
    ]
    private string|null $quoteId = null;

    #[Column(name: "quote_text", type: 'string', length: 300, unique: true, nullable: false)]
    private string $quoteText;

    #[ManyToOne(targetEntity: Author::class, inversedBy: 'quotes')]
    #[JoinColumn(name: 'author_id', referencedColumnName: 'author_id')]
    private Author|null $quoteAuthor = null;

    #[Column(name: 'created_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    /**
     * Bidirectional - Many quotes can be viewed by many users (INVERSE SIDE)
     *
     * @var Collection<int, User>
     */
    #[ManyToMany(targetEntity: User::class, mappedBy: 'quotes')]
    private Collection $userQuoteViews;

    public function __construct(string $quoteText, Author $quoteAuthor)
    {
        $this->createdAt = new DateTimeImmutable('now');
        $this->quoteText = $quoteText;
        $this->quoteAuthor = $quoteAuthor;
        $this->userQuoteViews = new ArrayCollection();
    }

    public function getQuoteId(): string
    {
        return $this->quoteId;
    }

    public function getQuoteText(): string
    {
        return $this->quoteText;
    }

    public function getQuoteAuthor(): ?Author
    {
        return $this->quoteAuthor;
    }

    public function getUserQuoteViews(): Collection
    {
        return $this->userQuoteViews;
    }

    public function addView(User $user)
    {
        $this->userQuoteViews[] = $user;
    }

}