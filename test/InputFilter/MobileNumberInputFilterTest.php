<?php

namespace AppTest\InputFilter;

use App\InputFilter\MobileNumberInputFilter;
use PHPUnit\Framework\TestCase;

class MobileNumberInputFilterTest extends TestCase
{
    /**
     * @dataProvider mobileNumberInputProvider
     */
    public function testThatOnlyNumbersInE164FormatAreMarkedAsValid(string $mobileNumber, bool $isValid)
    {
        $inputFilter = new MobileNumberInputFilter();
        $inputFilter->setData([
            'mobileNumber' => $mobileNumber
        ]);
        $this->assertSame($isValid, $inputFilter->isValid());
    }

    public static function mobileNumberInputProvider(): array
    {
        return [
            [
                '+551155256325',
                true
            ],
            [
                '+442071838750',
                true
            ],
            [
                '+14155552671',
                true
            ],
            [
                '00551155256325',
                false
            ],
            [
                '02071838750',
                false
            ],
            [
                '(01) 04155552671',
                false
            ],
        ];
    }
}
