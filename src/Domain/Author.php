<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Ramsey\Uuid\Doctrine\UuidGenerator;

#[ORM\Entity, ORM\Table(name: 'quote_authors')]
class Author
{
    use TimestampableEntity;

    #[
        ORM\Id,
        ORM\Column(name: "author_id", type: 'uuid'),
        ORM\GeneratedValue(strategy: 'CUSTOM'),
        ORM\CustomIdGenerator(class: UuidGenerator::class)
    ]
    private string|null $authorId = null;

    #[ORM\Column(name: "full_name", type: 'string', length: 200, unique: true, nullable: false)]
    private string $fullName;

    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Quote::class)]
    private Collection $quotes;

    public function __construct(string $fullName)
    {
        $this->fullName = $fullName;
        $this->quotes = new ArrayCollection();
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function isValid(): bool
    {
        $fullName = new Input('fullName');
        $fullName->getValidatorChain()
            ->attach(new NotEmpty());

        $inputFilter = new InputFilter();
        $inputFilter->add($fullName);

        $inputFilter->setData([
            'fullName' => $this->fullName
        ]);

        return $inputFilter->isValid();
    }


}