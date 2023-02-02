<?php

declare(strict_types=1);

namespace App;

use App\Domain\Quote;
use App\Domain\User;
use Doctrine\ORM\EntityManager;

class QuoteService
{
    private EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array<int,Quote>
     */
    private function getViewedQuotesForUser(User $user): array
    {
        $quotes = [];
        $viewedQuotes = $user->getViewedQuotes();
        foreach ($viewedQuotes as $viewedQuote) {
            /** @var Quote $viewedQuote */
            $quotes[] = $viewedQuote->getQuoteId();
        }

        return $quotes;
    }

    public function getRandomQuoteForUser(User $user): Quote|null
    {
        $queryBuilder = $this->em->createQueryBuilder();
        return $queryBuilder
            ->select('q')
            ->from(Quote::class, 'q')
            ->where($queryBuilder->expr()->notIn('q.quoteId', $this->getViewedQuotesForUser($user)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function markQuoteAsSentToUser(User $user, Quote $quote): bool
    {
        $quote->addView($user);
        $user->addViewedQuote($quote);

        $this->em->persist($quote);
        $this->em->flush();

        return true;
    }

}