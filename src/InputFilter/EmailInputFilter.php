<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\EmailAddress;

class EmailInputFilter extends InputFilter
{
    public function __construct()
    {
        $emailInput = new Input('email');
        $emailInput
            ->getValidatorChain()
            ->attachByName(EmailAddress::class);
        $emailInput
            ->getFilterChain()
            ->attachByName(StripTags::class)
            ->attachByName(StripNewlines::class)
            ->attachByName(StringTrim::class);

        $this->add($emailInput);
    }
}