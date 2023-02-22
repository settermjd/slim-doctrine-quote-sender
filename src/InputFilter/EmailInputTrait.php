<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripNewlines;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\EmailAddress;

trait EmailInputTrait
{
    public function getEmailInput(): Input
    {
        $emailInput = new Input('emailAddress');
        $emailInput
            ->getValidatorChain()
            ->attachByName(EmailAddress::class);
        $emailInput
            ->getFilterChain()
            ->attachByName(StripTags::class)
            ->attachByName(StripNewlines::class)
            ->attachByName(StringTrim::class);

        return $emailInput;
    }
}