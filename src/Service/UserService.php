<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Quote;
use App\Domain\User;
use App\QuoteType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

class UserService
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function create(string $fullName = null, string $emailAddress = null, string $mobileNumber = null): User
    {
        $newUser = new User($fullName, $emailAddress, $mobileNumber);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function createWithMobileNumber(string $mobileNumber): User
    {
        $user = $this->findByMobileNumber($mobileNumber);
        if ($user instanceof User) {
            return $user;
        }

        $newUser = new User(null, null, $mobileNumber);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function createWithEmailAddress(string $emailAddress): User
    {
        $user = $this->findByEmailAddress($emailAddress);
        if ($user instanceof User) {
            return $user;
        }

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

    public function getQuotes(User $user, QuoteType $quoteType): Collection
    {
        $quotes = $this->getViewedQuoteIDs($user);

        if ($quoteType === QuoteType::Viewed) {
           return $user->getViewedQuotes();
        }

        if ($quoteType === QuoteType::Unviewed) {
            if (empty($quotes)) {
                return new ArrayCollection();
            }

            $queryBuilder = $this->em->createQueryBuilder();
            $results = $queryBuilder
                ->select('q')
                ->from(Quote::class, 'q')
                ->where($queryBuilder->expr()->notIn('q.quoteId', $quotes))
                ->getQuery()
                ->getResult();

            return new ArrayCollection($results);
        }

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
        $this->em->flush();

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
        $this->em->flush();

        return true;
    }

    /**
     * @return array<int,Quote>
     */
    public function getViewedQuoteIDs(User $user): array
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