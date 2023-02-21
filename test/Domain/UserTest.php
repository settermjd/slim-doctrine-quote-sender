<?php

namespace AppTest\Domain;

use App\Domain\User;
use App\InputFilter\UserInputFilter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserTest extends TestCase
{
    public function testCannotCreateUserWithNonE164MobileNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Entity is not in a valid state. Reason: Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.");

        $uuid = Uuid::uuid4();
        $user = new User(
            new UserInputFilter(),
            $uuid->toString(),
            null,
            null,
            '0014155552671'
        );
    }
}
