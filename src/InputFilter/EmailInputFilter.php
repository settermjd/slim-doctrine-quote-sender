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
    use EmailInputTrait;

    public function __construct()
    {
        $this->add($this->getEmailInput());
    }
}