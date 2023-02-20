<?php

namespace App\Repository;

use App\Domain\Quote;
use App\Domain\User;
use Doctrine\ORM\EntityRepository;

class QuoteRepository extends EntityRepository
{
    public const SMS_MAX_LENGTH = 160;

    public function getRandomQuoteForUser(User $user): Quote|null
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('q')
            ->from(Quote::class, 'q')
            ->setMaxResults(1);

        if (! empty($user->getViewedQuoteIDs())) {
            $queryBuilder->where(
                $queryBuilder
                    ->expr()
                    ->notIn('q.quoteId', $user->getViewedQuoteIDs())
            );
        }

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getRandomQuoteForMobileUser(User $user): Quote|null
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        return $queryBuilder
            ->select('q')
            ->from(Quote::class, 'q')
            ->where(
                $queryBuilder->expr()->lte(
                    $queryBuilder->expr()->length('q.quoteText'),
                    self::SMS_MAX_LENGTH
                )
            )
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}