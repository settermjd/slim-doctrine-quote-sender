<?php

namespace AppTest\Domain;

use App\Domain\Author;
use App\Domain\Quote;
use PHPUnit\Framework\TestCase;

class QuoteTest extends TestCase
{
    /**
     * @dataProvider quoteData
     */
    public function testCanRetrievePrintableQuote(string $quoteAuthor, string $quoteText)
    {
        $quote = new Quote($quoteText, new Author($quoteAuthor));
        $this->assertSame(
            sprintf('%s - %s', $quoteText, $quoteAuthor),
            $quote->getPrintableQuote()
        );
    }

    public static function quoteData()
    {
        return [
            [
                'Antoine de Saint-Exupery',
                "Perfection is achieved not when there is nothing more to add, but rather when there is nothing more to take away.",
            ],
            [
                'Brian Kernighan',
                "Don't comment bad code - rewrite it.",
            ]
        ];
    }
}
