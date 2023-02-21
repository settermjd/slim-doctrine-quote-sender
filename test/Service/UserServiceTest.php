<?php

namespace AppTest\Service;

use App\Domain\User;
use App\QuoteType;
use App\Service\UserService;
use AppTest\Data\Fixtures\QuoteAuthorDataLoader;
use AppTest\Data\Fixtures\QuoteDataLoader;
use AppTest\Data\Fixtures\UserDataLoader;
use AppTest\Data\Fixtures\UserQuoteViewDataLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;

class UserServiceTest extends TestCase
{
    private EntityManager|null $entityManager;
    private ORMPurger $purger;

    public function setUp(): void
    {
        /** @var ContainerInterface $container */
        $container = require_once __DIR__ . '/../../container.php';

        $loader = new Loader();
        $loader->addFixture(new UserDataLoader());
        $loader->addFixture(new QuoteAuthorDataLoader());
        $loader->addFixture(new QuoteDataLoader());
        $loader->addFixture(new UserQuoteViewDataLoader());

        $this->entityManager = $container->get(EntityManager::class);

        $this->purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $this->purger);
        $executor->execute($loader->getFixtures());
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->purger->purge();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * @dataProvider createUserDataProvider
     */
    public function testUserServiceCanCreateNewUserWithAnyCombinationOfUserDetails(
        string $userId,
        string $fullName = null,
        string $emailAddress = null,
        string $mobileNumber = null
    ) {
        $userService = new UserService($this->entityManager);
        $user = $userService->create($userId, $fullName, $emailAddress, $mobileNumber);

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($this->entityManager->contains($user));
    }

    public static function createUserDataProvider(): array
    {
        return [
            [
                Uuid::uuid4()->toString(),
                'User 11',
                'user11@example.org',
                null
            ],
            [
                Uuid::uuid4()->toString(),
                'User 12',
                'user12@example.org',
                '+14155552691'
            ],
            [
                Uuid::uuid4()->toString(),
                null,
                'user13@example.org',
                null
            ],
            [
                Uuid::uuid4()->toString(),
                null,
                null,
                '+14155552692'
            ],
            [
                Uuid::uuid4()->toString(),
                'User 16',
                null,
                null,
            ],
        ];
    }

    /**
     * @dataProvider createAndUpdateUserDataProvider
     */
    public function testWillCheckIfUserAlreadyExistsBeforeCreating(array $userData) {
        $userService = new UserService($this->entityManager);

        if (array_key_exists('mobileNumber', $userData)) {
            $user = $userService->createWithMobileNumber($userData['mobileNumber']);
            $this->assertInstanceOf(User::class, $user);
            $this->assertTrue($this->entityManager->contains($user));
        }

        if (array_key_exists('emailAddress', $userData)) {
            $user = $userService->createWithEmailAddress($userData['emailAddress']);
            $this->assertInstanceOf(User::class, $user);
            $this->assertTrue($this->entityManager->contains($user));
        }
    }

    public static function createAndUpdateUserDataProvider(): array
    {
        return [
            [
                [
                    'emailAddress' => 'user1@example.org',
                ]
            ],
            [
                [
                    'mobileNumber' => '+14155552672'
                ]
            ],
            [
                [
                    'emailAddress' => 'user5@example.org',
                ]
            ],
        ];
    }

    public function testCanCreateNewUserWithMobileNumber()
    {
        $userService = new UserService($this->entityManager);
        $user = $userService->createWithMobileNumber('+14155552671');

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($this->entityManager->contains($user));
    }

    public function testCanCreateNewUserWithEmailAddress()
    {
        $userService = new UserService($this->entityManager);
        $user = $userService->createWithEmailAddress('email-address-user@example.org');

        $this->assertInstanceOf(User::class, $user);
        $this->assertTrue($this->entityManager->contains($user));
    }

    /**
     * @dataProvider invalidMobilePhoneNumberDataProvider
     */
    public function testUserServiceCannotCreateNewUserWithInvalidMobileNumber(
        string $userId,
        string $fullName,
        string $emailAddress = null,
        string $mobileNumber = null
    ) {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Entity is not in a valid state. Reason: Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.'
        );
        $userService = new UserService($this->entityManager);
        $userService->create($userId, $fullName, $emailAddress, $mobileNumber);
    }

    public static function invalidMobilePhoneNumberDataProvider(): array
    {
        return [
            [
                Uuid::uuid4()->toString(),
                'User 3',
                'user3@example.org',
                '00114155552671'
            ],
            [
                Uuid::uuid4()->toString(),
                'User 4',
                'user4@example.org',
                '04155552671'
            ],
        ];
    }

    /**
     * @dataProvider invalidEmailAddressDataProvider
     */
    public function testUserServiceCannotCreateNewUserWithInvalidEmailAddress(
        string $userId,
        string $fullName,
        string $emailAddress = null,
        string $mobileNumber = null
    ) {
        $this->expectException(\InvalidArgumentException::class);
        $userService = new UserService($this->entityManager);
        $userService->create($userId, $fullName, $emailAddress, $mobileNumber);
    }

    public static function invalidEmailAddressDataProvider(): array
    {
        return [
            [
                Uuid::uuid4()->toString(),
                'User 3',
                'user3@org',
                '+14155552671'
            ],
            [
                Uuid::uuid4()->toString(),
                'User 4',
                'user4@example',
                null
            ],
        ];
    }

    public function testCanFindUserByMobileNumber()
    {
        $userService = new UserService($this->entityManager);
        $user = $userService->findByMobileNumber('+14155552672');
        $this->assertInstanceOf(User::class, $user);
    }

    public function testCanFindUserByEmailAddress()
    {
        $userService = new UserService($this->entityManager);
        $user = $userService->findByEmailAddress('user3@example.org');
        $this->assertInstanceOf(User::class, $user);
    }

    public function testWillReturnNullIfUserCannotBeFoundByMobileNumber()
    {
        $this->assertNull(
            (new UserService($this->entityManager))
                ->findByMobileNumber('+14155552679')
        );
    }

    public function testWillReturnNullIfUserCannotBeFoundByEmailAddress()
    {
        $this->assertNull(
            (new UserService($this->entityManager))
                ->findByEmailAddress('non-existent-user@example.org')
        );
    }


    public function testCanDeleteUserByMobileNumber()
    {
        $mobileNumber = '+14155552672';

        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['mobileNumber' => $mobileNumber]);

        $this->assertTrue(
            (new UserService($this->entityManager))
                ->removeByMobileNumber($mobileNumber)
        );
        $this->assertFalse($this->entityManager->contains($user));
    }

    public function testCanDeleteUserByEmailAddress()
    {
        $emailAddress = 'user3@example.org';

        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['emailAddress' => $emailAddress]);

        $this->assertTrue(
            (new UserService($this->entityManager))
                ->removeByEmailAddress($emailAddress)
        );
        $this->assertFalse($this->entityManager->contains($user));
    }
}
