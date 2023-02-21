<?php

namespace AppTest\InputFilter;

use App\InputFilter\UserInputFilter;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UserInputFilterTest extends TestCase
{
    /**
     * @dataProvider userDataProvider
     */
    public function testCanValidateProperties(array $properties, bool $isValid)
    {
        $inputFilter = new UserInputFilter();
        $inputFilter->setData([
            'userId' => $properties['userId'],
            'emailAddress' => $properties['emailAddress'] ?? null,
            'fullName' => $properties['fullName'] ?? null,
            'mobileNumber' => $properties['mobileNumber'] ?? null,
        ]);

        $this->assertSame($isValid, $inputFilter->isValid(), var_export($inputFilter->getMessages(), TRUE));
    }

    public static function userDataProvider(): array
    {
        return [
            [
                [
                    'userId' => Uuid::uuid4()->toString(),
                    'fullName' => "User 1",
                    'mobileNumber' => '+14155552671',
                    'emailAddress' => 'test-user@gmail.com',
                ],
                true
            ],
            [
                [
                    'userId' => null,
                    'fullName' => "User 1",
                    'mobileNumber' => '+14155552671',
                    'emailAddress' => 'test-user@gmail.com',
                ],
                false
            ],
            [
                [
                    'userId' => Uuid::uuid4()->toString(),
                    'fullName' => "User 1",
                    'mobileNumber' => '+14155552671',
                ],
                true
            ],
            [
                [
                    'userId' => Uuid::uuid4()->toString(),
                    'fullName' => "User 1",
                    'mobileNumber' => '0014155552671',
                ],
                false
            ],
            [
                [
                    'userId' => Uuid::uuid4()->toString(),
                    'fullName' => "User 1",
                    'emailAddress' => 'test-user@gmail.com',
                ],
                true
            ],
            [
                [
                    'userId' => Uuid::uuid4()->toString(),
                    'fullName' => "User 1",
                    'emailAddress' => 'test-user@gmail',
                ],
                false
            ],
            [
                [
                    'userId' => Uuid::uuid4()->toString(),
                ],
                true
            ],
        ];
    }
}
