<?php

namespace App\InputFilter;

use Laminas\InputFilter\Input;
use Laminas\Validator\Regex;

trait MobileInputTrait
{
    public const REGEX_E164 = '/^\+[1-9]\d{1,14}$/';

    public function getMobileNumberInput(): Input
    {
        $mobileNumberValidator = new Regex(self::REGEX_E164);
        $mobileNumberValidator->setMessage(
            'Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.',
            Regex::NOT_MATCH
        );

        $mobileNumber = new Input('mobileNumber');
        $mobileNumber
            ->getValidatorChain()
            ->attach($mobileNumberValidator);
        $mobileNumber->setAllowEmpty(true);

        return $mobileNumber;
    }
}