<?php

declare(strict_types=1);

namespace App\InputFilter;

use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Uuid;

class UserInputFilter extends InputFilter
{
    use EmailInputTrait, MobileInputTrait;

    public function __construct()
    {
        $userId = new Input('userId');
        $userId->setAllowEmpty(false);
        $userId
            ->getValidatorChain()
            ->attach(new Uuid());

        $fullName = new Input('fullName');
        $fullName->setAllowEmpty(true);

        $emailAddress = $this->getEmailInput();
        $emailAddress->setAllowEmpty(true);

        $mobileNumber = $this->getMobileNumberInput();
        $mobileNumber->setAllowEmpty(true);

        $this->add($userId);
        $this->add($fullName);
        $this->add($emailAddress);
        $this->add($mobileNumber);
    }
}