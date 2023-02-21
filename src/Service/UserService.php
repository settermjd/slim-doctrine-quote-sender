<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\User;
use App\InputFilter\UserInputFilter;
use Doctrine\ORM\EntityManager;
use Ramsey\Uuid\Uuid;

class UserService
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function create(
        string $userId,
        string $fullName = null,
        string $emailAddress = null,
        string $mobileNumber = null
    ): User
    {
        $newUser = new User(new UserInputFilter(), $userId, $fullName, $emailAddress, $mobileNumber);

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function createWithMobileNumber(string $mobileNumber): User
    {
        $user = $this->findByMobileNumber($mobileNumber);
        if ($user instanceof User) {
            return $user;
        }

        $newUser = new User(
            new UserInputFilter(),
            Uuid::uuid4()->toString(),
            null,
            null,
            $mobileNumber
        );

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function createWithEmailAddress(string $emailAddress): User
    {
        $user = $this->findByEmailAddress($emailAddress);
        if ($user instanceof User) {
            return $user;
        }

        $newUser = new User(
            new UserInputFilter(),
            Uuid::uuid4()->toString(),
            null,
            $emailAddress,
            null
        );

        $this->em->persist($newUser);
        $this->em->flush();

        return $newUser;
    }

    public function findByMobileNumber(string $mobileNumber): User|null
    {
        return $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'mobileNumber' => $mobileNumber,
                ]
            );
    }

    public function findByEmailAddress(string $emailAddress): User|null
    {
        return $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'emailAddress' => $emailAddress,
                ]
            );
    }


    public function removeByMobileNumber(string $mobileNumber): bool
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'mobileNumber' => $mobileNumber,
                ]
            );
        $this->em->remove($user);
        $this->em->flush();

        return true;
    }

    public function removeByEmailAddress(string $emailAddress): bool
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'emailAddress' => $emailAddress,
                ]
            );
        $this->em->remove($user);
        $this->em->flush();

        return true;
    }

}