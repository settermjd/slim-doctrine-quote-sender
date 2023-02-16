<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity, ORM\Table(name: 'quotes')]
class Quote
{
    #[
        ORM\Id,
        ORM\Column(name: "quote_id", type: 'uuid'),
        ORM\GeneratedValue(strategy: 'CUSTOM'),
        ORM\CustomIdGenerator(class: UuidGenerator::class)
    ]
    private string|null $quoteId = null;

    #[ORM\Column(name: "quote_text", type: 'string', length: 300, unique: true, nullable: false)]
    private string $quoteText;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'quotes')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'author_id')]
    private Author|null $quoteAuthor = null;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    /**
     * Bidirectional - Many quotes can be viewed by many users (INVERSE SIDE)
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'quotes')]
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

    /**
     * Returns a string comprised of the quote and the author, used in a typical quote style.
     */
    public function getPrintableQuote(): string
    {
        return sprintf('%s - %s', $this->quoteText, $this->quoteAuthor->getFullName());
    }

}