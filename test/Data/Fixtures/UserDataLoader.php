<?php

namespace AppTest\Data\Fixtures;

use App\Domain\User;
use App\InputFilter\UserInputFilter;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class UserDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $data = [
            [
                'userId' => Uuid::uuid4()->toString(),
                'fullName' => 'User 1',
                'emailAddress' => 'user1@example.org',
                'mobileNumber' => null,
            ],
            [
                'userId' => Uuid::uuid4()->toString(),
                'fullName' => 'User 2',
                'emailAddress' => null,
                'mobileNumber' => '+14155552672',
            ],
            [
                'userId' => Uuid::uuid4()->toString(),
                'fullName' => 'User 3',
                'emailAddress' => 'user3@example.org',
                'mobileNumber' => null,
            ],
            [
                'userId' => Uuid::uuid4()->toString(),
                'fullName' => 'User 4',
                'emailAddress' => 'user4@example.org',
                'mobileNumber' => null,
            ],
            [
                'userId' => Uuid::uuid4()->toString(),
                'fullName' => 'User 5',
                'emailAddress' => null,
                'mobileNumber' => '+14155552673',
            ],
            [
                'userId' => Uuid::uuid4()->toString(),
                'fullName' => 'User 6',
                'emailAddress' => null,
                'mobileNumber' => '+14155552674',
            ],
        ];

        foreach ($data as $datum) {
            $user = new User(
                $datum['userId'],
                $datum['fullName'],
                $datum['emailAddress'],
                $datum['mobileNumber']
            );
            $manager->persist($user);
            $manager->flush();

            $this->addReference(
                sprintf('%s-user', str_replace(' ', '-', strtolower($user->getFullName()))),
                $user
            );
        }
    }
}