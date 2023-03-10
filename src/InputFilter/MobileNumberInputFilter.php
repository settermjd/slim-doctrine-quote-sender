<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Regex;

class MobileNumberInputFilter extends InputFilter
{
    use MobileInputTrait;

    public function __construct()
    {
        $mobileNumberInput = new Input('mobileNumber');
        $mobileNumberInput
            ->getValidatorChain()
            ->attach(new Regex(self::REGEX_E164));
        $mobileNumberInput
            ->getFilterChain()
            ->attachByName(StripTags::class)
            ->attachByName(StripNewlines::class)
            ->attachByName(StringTrim::class);

        $this->add($mobileNumberInput);
    }
}