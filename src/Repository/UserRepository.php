<?php

namespace App\Repository;

use App\Domain\Quote;
use App\Domain\QuoteType;
use App\Domain\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getQuotes(User $user, QuoteType $quoteType): Collection
    {
        $quoteIDs = $user->getViewedQuoteIDs($user);

        if ($quoteType === QuoteType::Unviewed) {
            if (empty($quoteIDs)) {
                return new ArrayCollection();
            }

            $queryBuilder = $this->_em->createQueryBuilder();
            $results = $queryBuilder
                ->select('q')
                ->from(Quote::class, 'q')
                ->where($queryBuilder->expr()->notIn('q.quoteId', $quoteIDs))
                ->getQuery()
                ->getResult();

            return new ArrayCollection($results);
        }

        return $user->getViewedQuotes();
    }


    /**
     * @return array<int,User>
     */
    public function getMobileUsers(): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        return $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
            ->where($queryBuilder->expr()->isNotNull('u.mobileNumber'))
            ->getQuery()
            ->getResult();
    }


    /**
     * @return array<int,User>
     */
    public function getEmailUsers(): array
    {
        $queryBuilder = $this->_em->createQueryBuilder();
        return $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
            ->where($queryBuilder->expr()->isNotNull('u.emailAddress'))
            ->getQuery()
            ->getResult();
    }
}