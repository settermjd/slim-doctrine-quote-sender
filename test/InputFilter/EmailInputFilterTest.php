<?php

namespace AppTest\InputFilter;

use App\InputFilter\EmailInputFilter;
use PHPUnit\Framework\TestCase;

class EmailInputFilterTest extends TestCase
{
    /**
     * @dataProvider emailInputProvider
     */
    public function testThatOnlyValidEmailsAreMarkedAsValid(string $email, bool $isValid)
    {
        $inputFilter = new EmailInputFilter();
        $inputFilter->setData([
            'email' => $email,
        ]);

        $this->assertSame($isValid, $inputFilter->isValid());
    }

    public function emailInputProvider()
    {
        return [
            [
                'user@example.org',
                true
            ],
            [
                'user@example',
                false
            ],
            [
                'user',
                false
            ],
            [
                '@example',
                false
            ],
        ];
    }
}
