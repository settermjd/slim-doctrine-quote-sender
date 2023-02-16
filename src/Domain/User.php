<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Validator\EmailAddress;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity, ORM\Table(name: 'quote_users')]
#[ORM\HasLifecycleCallbacks]
class User
{
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

    #[ORM\Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $registeredAt;

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

    public function __construct(string $fullName = null, string $emailAddress = null, string $mobileNumber = null)
    {
        $this->registeredAt = new DateTimeImmutable('now');
        $this->emailAddress = $emailAddress;
        $this->fullName = $fullName;
        $this->mobileNumber = $mobileNumber;
        $this->quotes = new ArrayCollection();
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

    public function addViewedQuote(Quote $quote)
    {
        $this->quotes->add($quote);
    }

    #[ORM\PrePersist, ORM\PreUpdate]
    public function validate()
    {
        if (! is_null($this->mobileNumber) && ! preg_match('/^\+[1-9]\d{1,14}$/', $this->mobileNumber)) {
            throw new \InvalidArgumentException(
                'Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.'
            );
        }

        if (
            ! is_null($this->emailAddress)
            && ! (new EmailAddress())->isValid($this->emailAddress)
        ) {
            throw new \InvalidArgumentException(
                'Email address must be a valid email address.'
            );
        }
    }

}