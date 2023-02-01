<?php

namespace AppTest\Data\Fixtures;

use App\Domain\Author;
use App\Domain\Quote;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class QuoteDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $data = [
            'Anonymous' => "Dont't worry if it doesn't work right. If everything did, you'd be out of a job.",
            'Brian Kernighan' => "Don't comment bad code - rewrite it.",
        ];

        foreach ($data as $authorName => $quoteText) {
            $author = $manager
                ->getRepository(Author::class)
                ->findOneBy(['fullName' => $authorName]);
            $quote = new Quote($quoteText, $author);
            $manager->persist($quote);
            $manager->flush();
        }

    }
}