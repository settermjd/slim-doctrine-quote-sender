<?php

declare(strict_types=1);

namespace App;

use App\Domain\Quote;
use App\Domain\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

class UserService
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function createNewUser(string $fullName, string $emailAddress, string $mobileNumber = null): User
    {
        $newUser = new User($fullName, $emailAddress, $mobileNumber);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function findByMobileNumber(string $mobileNumber): User
    {
        return $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'mobileNumber' => $mobileNumber,
                ]
            );
    }

    public function findByEmailAddress(string $emailAddress): User
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
    public function getUnviewedQuotesForUser(User $user): array
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $viewedQuotes = $user->getViewedQuotes();
        $quotes = [];
        foreach ($viewedQuotes as $viewedQuote) {
            /** @var Quote $viewedQuote */
            $quotes[] = $viewedQuote->getQuoteId();
        }

        return $queryBuilder
            ->select('q')
            ->from(Quote::class, 'q')
            ->where($queryBuilder->expr()->notIn('q.quoteId', $quotes))
            ->getQuery()
            ->getResult();
    }

}