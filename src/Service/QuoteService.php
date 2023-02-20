<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Quote;
use App\Domain\User;
use Doctrine\ORM\EntityManager;

class QuoteService
{
    public function __construct(private EntityManager $em)
    {
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