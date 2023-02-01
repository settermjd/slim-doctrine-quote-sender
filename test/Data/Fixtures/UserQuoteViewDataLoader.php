<?php

namespace AppTest\Data\Fixtures;

use App\Domain\Quote;
use App\Domain\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserQuoteViewDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference('user-2-user');

        /** @var Quote $quote */
        $quote = $manager
            ->getRepository(Quote::class)
            ->findOneBy(
                [
                    'quoteText' => "Dont't worry if it doesn't work right. If everything did, you'd be out of a job."
                ]
            );
        $quote->addView($user);
        $user->addViewedQuote($quote);

        $manager->persist($quote);
        $manager->flush();
    }
}