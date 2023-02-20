<?php

declare(strict_types=1);

namespace App\Domain;

use App\Repository\QuoteRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity(repositoryClass: QuoteRepository::class), ORM\Table(name: 'quotes')]
class Quote
{
    use TimestampableEntity;

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

    /**
     * Bidirectional - Many quotes can be viewed by many users (INVERSE SIDE)
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'quotes')]
    private Collection $userQuoteViews;

    public function __construct(string $quoteText, Author $quoteAuthor)
    {
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

    public function isValid(): bool
    {
        $quoteText = new Input('quoteText');
        $quoteText->getValidatorChain()
            ->attach(new NotEmpty());

        $inputFilter = new InputFilter();
        $inputFilter->add($quoteText);

        $inputFilter->setData([
            'quoteText' => $this->quoteText
        ]);

        return $inputFilter->isValid() && $this->quoteAuthor->isValid();
    }

}