<?php

namespace App\InputFilter;

use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Regex;

class UserInputFilter extends InputFilter
{
    public function __construct()
    {
        $fullName = new Input('fullName');
        $fullName->setAllowEmpty(true);

        $emailAddress = new Input('emailAddress');
        $emailAddress
            ->getValidatorChain()
            ->attach(new EmailAddress());
        $emailAddress->setAllowEmpty(true);

        $mobileNumberValidator = new Regex('/^\+[1-9]\d{1,14}$/');
        $mobileNumberValidator->setMessage(
            'Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.',
            Regex::NOT_MATCH
        );

        $mobileNumber = new Input('mobileNumber');
        $mobileNumber
            ->getValidatorChain()
            ->attach($mobileNumberValidator);
        $mobileNumber->setAllowEmpty(true);

        $this->add($fullName);
        $this->add($emailAddress);
        $this->add($mobileNumber);
    }
}