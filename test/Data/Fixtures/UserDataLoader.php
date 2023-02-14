<?php

namespace AppTest\Data\Fixtures;

use App\Domain\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserDataLoader extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $data = [
            [
                'fullName' => 'User 1',
                'emailAddress' => 'user1@example.org',
                'mobileNumber' => null,
            ],
            [
                'fullName' => 'User 2',
                'emailAddress' => null,
                'mobileNumber' => '+14155552672',
            ],
            [
                'fullName' => 'User 3',
                'emailAddress' => 'user3@example.org',
                'mobileNumber' => null,
            ],
            [
                'fullName' => 'User 4',
                'emailAddress' => 'user4@example.org',
                'mobileNumber' => null,
            ],
            [
                'fullName' => 'User 5',
                'emailAddress' => null,
                'mobileNumber' => '+14155552673',
            ],
            [
                'fullName' => 'User 6',
                'emailAddress' => null,
                'mobileNumber' => '+14155552674',
            ],
        ];

        foreach ($data as $datum) {
            $user = new User($datum['fullName'], $datum['emailAddress'], $datum['mobileNumber']);
            $manager->persist($user);
            $manager->flush();

            $this->addReference(
                sprintf('%s-user', str_replace(' ', '-', strtolower($user->getFullName()))),
                $user
            );
        }
    }
}