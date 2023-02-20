<?php

namespace AppTest\Domain;

use App\Domain\Author;
use App\Domain\Quote;
use PHPUnit\Framework\TestCase;

class QuoteTest extends TestCase
{
    private Author|null $quoteAuthor;

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

    /**
     * @dataProvider quoteDataProvider
     */
    public function testCanValidateProperties(array $properties, bool $isValid)
    {
        $quote = new Quote($properties['quoteText'], $properties['quoteAuthor']);
        $this->assertSame($isValid, $quote->isValid());
    }

    public static function quoteDataProvider()
    {
        return [
            [
                [
                    'quoteText' => "Don't comment bad code - rewrite it.",
                    'quoteAuthor' => new Author('Brian Kernighan'),
                ],
                true,
            ]
        ];
    }
}
