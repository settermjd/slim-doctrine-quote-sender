<?php

declare(strict_types=1);

namespace App\Domain;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Laminas\InputFilter\InputFilterInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity(repositoryClass: UserRepository::class), ORM\Table(name: 'quote_users')]
#[ORM\HasLifecycleCallbacks]
class User
{
    use TimestampableEntity;

    #[
        ORM\Id,
        ORM\Column(name: "user_id", type: 'uuid'),
        ORM\GeneratedValue(strategy: 'CUSTOM'),
        ORM\CustomIdGenerator(class: UuidGenerator::class)
    ]
    private string|null $userId = null;

    #[ORM\Column(name: "full_name", type: 'string', length: 36, unique: true, nullable: true)]
    private string|null $fullName;

    #[ORM\Column(name: "mobile_number", type: 'string', length: 18, unique: true, nullable: true)]
    private string|null $mobileNumber = null;

    #[ORM\Column(name: "email_address", type: 'text', unique: true, nullable: true)]
    private string|null $emailAddress = null;

    /**
     * Bidirectional - Many users have viewed many quotes (OWNING SIDE)
     *
     * @var Collection<int, Quote>
     */
    #[ORM\ManyToMany(targetEntity: Quote::class, inversedBy: 'userQuoteViews')]
    #[ORM\JoinTable(name: 'user_quote_views')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id')]
    #[ORM\InverseJoinColumn(name: 'quote_id', referencedColumnName: 'quote_id')]
    private Collection $quotes;

    public function __construct(
        private InputFilterInterface $inputFilter,
        string $userId,
        string $fullName = null,
        string $emailAddress = null,
        string $mobileNumber = null
    ) {
        $this->userId = $userId;
        $this->emailAddress = $emailAddress;
        $this->fullName = $fullName;
        $this->mobileNumber = $mobileNumber;
        $this->quotes = new ArrayCollection();

        if (! $this->isValid()) {
            $reason = '';
            $messages = $this->inputFilter->getMessages();

            if (array_key_exists('mobileNumber', $messages)) {
                $reason .= implode(', ', $messages['mobileNumber']);
            }

            if (array_key_exists('emailAddress', $messages)) {
                $reason .= implode(', ', $messages['emailAddress']);
            }

            throw new \InvalidArgumentException(
                sprintf('Entity is not in a valid state. Reason: %s', $reason)
            );
        }
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    public function getViewedQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addViewedQuote(Quote $quote): void
    {
        $this->quotes->add($quote);
    }

    /**
     * @return array<int,Quote>
     */
    public function getViewedQuoteIDs(): array
    {
        $quotes = [];
        $viewedQuotes = $this->getViewedQuotes();
        foreach ($viewedQuotes as $viewedQuote) {
            /** @var Quote $viewedQuote */
            $quotes[] = $viewedQuote->getQuoteId();
        }

        return $quotes;
    }

    public function isValid(): bool
    {
        $this->inputFilter->setData([
            'userId' => $this->userId,
            'emailAddress' => $this->emailAddress,
            'fullName' => $this->fullName,
            'mobileNumber' => $this->mobileNumber,
        ]);

        return $this->inputFilter->isValid();
    }

}