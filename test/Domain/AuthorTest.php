<?php

namespace AppTest\Domain;

use App\Domain\Author;
use PHPUnit\Framework\TestCase;

class AuthorTest extends TestCase
{
    /**
     * @dataProvider authorDataProvider
     */
    public function testCanValidateProperties(array $properties, bool $isValid)
    {
        $author = new Author($properties['fullName']);
        $this->assertSame($isValid, $author->isValid());
    }

    public static function authorDataProvider(): array
    {
        return [
            [
                [
                    'fullName' => "User 1",
                ],
                true
            ],
            [
                [
                    'fullName' => "",
                ],
                false
            ],
        ];
    }
}
