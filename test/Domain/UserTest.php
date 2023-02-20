<?php

namespace AppTest\Domain;

use App\Domain\User;
use App\InputFilter\UserInputFilter;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @dataProvider userDataProvider
     */
    public function testCanValidateProperties(array $properties, bool $isValid)
    {
        $user = new User(
            new UserInputFilter(),
            $properties['fullName'] ?? null,
            $properties['emailAddress'] ?? null,
            $properties['mobileNumber'] ?? null
        );

        $this->assertSame($isValid, $user->isValid());
    }

    public static function userDataProvider(): array
    {
        return [
            [
                [
                    'fullName' => "User 1",
                    'mobileNumber' => '+14155552671',
                    'emailAddress' => 'test-user@gmail.com',
                ],
                true
            ],
            [
                [
                    'fullName' => "User 1",
                    'mobileNumber' => '+14155552671',
                ],
                true
            ],
            [
                [
                    'fullName' => "User 1",
                    'emailAddress' => 'test-user@gmail.com',
                ],
                true
            ],
            [
                [],
                true
            ],
        ];
    }

    public function testCannotCreateUserWithNonE164MobileNumber()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Entity is not in a valid state. Reason: Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.");

        $user = new User(
            new UserInputFilter(),
            null,
            null,
            '0014155552671'
        );
    }

}
