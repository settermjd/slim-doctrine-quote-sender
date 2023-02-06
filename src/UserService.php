<?php

declare(strict_types=1);

namespace App;

use App\Domain\Quote;
use App\Domain\User;
use Doctrine\ORM\EntityManager;

class UserService
{

    public function __construct(private EntityManager $em)
    {
    }

    public function create(string $fullName, string $emailAddress, string $mobileNumber = null): User
    {
        $newUser = new User($fullName, $emailAddress, $mobileNumber);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function createWithMobileNumber(string $mobileNumber): User
    {
        $newUser = new User(null, null, $mobileNumber);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function createWithEmailAddress(string $emailAddress): User
    {
        $newUser = new User(null, $emailAddress, null);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function findByMobileNumber(string $mobileNumber): User|null
    {
        return $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'mobileNumber' => $mobileNumber,
                ]
            );
    }

    public function findByEmailAddress(string $emailAddress): User|null
    {
        return $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'emailAddress' => $emailAddress,
                ]
            );
    }

    /**
     * @return array<int,Quote>
     */
    public function getQuotes(User $user, QuoteType $quoteType): array
    {
        $quotes = $this->getViewedQuotes($user);

        $queryBuilder = $this->em->createQueryBuilder();
        $wherePredicate = match($quoteType) {
            QuoteType::Viewed => $queryBuilder->expr()->in('q.quoteId', $quotes),
            QuoteType::Unviewed => $queryBuilder->expr()->notIn('q.quoteId', $quotes),
        };

        return $queryBuilder
            ->select('q')
            ->from(Quote::class, 'q')
            ->where($wherePredicate)
            ->getQuery()
            ->getResult();
    }

    public function removeByMobileNumber(string $mobileNumber): bool
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'mobileNumber' => $mobileNumber,
                ]
            );
        $this->em->remove($user);

        return true;
    }

    public function removeByEmailAddress(string $emailAddress): bool
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'emailAddress' => $emailAddress,
                ]
            );
        $this->em->remove($user);

        return true;
    }

    /**
     * @return array<int,Quote>
     */
    public function getViewedQuotes(User $user): array
    {
        $quotes = [];
        $viewedQuotes = $user->getViewedQuotes();
        foreach ($viewedQuotes as $viewedQuote) {
            /** @var Quote $viewedQuote */
            $quotes[] = $viewedQuote->getQuoteId();
        }

        return $quotes;
    }

}