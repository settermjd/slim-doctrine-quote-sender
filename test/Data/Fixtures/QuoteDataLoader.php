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
            'Antoine de Saint-Exupery' => "Perfection is achieved not when there is nothing more to add, but rather when there is nothing more to take away.",
            'Brian Kernighan' => "Don't comment bad code - rewrite it.",
            'Chris Heilman' => "Java is to JavaScript what car is to Carpet.",
            'Dan Salomon' => "Sometimes it pays to stay in bed on Monday, rather than spending the rest of the week debugging Monday’s code.",
            'John Johnson' => "First, solve the problem. Then, write the code.",
            'Martin Fowler' => "Any fool can write code that a computer can understand. Good programmers write code that humans can understand.",
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