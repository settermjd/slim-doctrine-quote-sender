<?php

namespace AppTest\Repository;

use App\Domain\QuoteType;
use App\Domain\User;
use App\Repository\UserRepository;
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

class UserRepositoryTest extends TestCase
{
    private EntityManager|null $entityManager;
    private ORMPurger $purger;
    private UserRepository $userRepository;

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
        $this->userRepository = $this->entityManager->getRepository(User::class);

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

    public function testCanRetrieveListOfUnviewedQuotes()
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy([
            'emailAddress' => 'user3@example.org',
        ]);

        $userService = new UserService($this->entityManager);
        $this->assertCount(
            6,
            $this->userRepository->getQuotes($user, QuoteType::Unviewed)
        );
    }

    public function testCanRetrieveListOfViewedQuotes()
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy([
            'emailAddress' => 'user3@example.org',
        ]);

        $userService = new UserService($this->entityManager);
        $this->assertCount(
            1,
            $this->userRepository->getQuotes($user, QuoteType::Viewed)
        );
    }

    public function testCanRetrieveAllMobileUsers()
    {
        $userService = new UserService($this->entityManager);
        $this->assertCount(
            3,
            $this->userRepository->getMobileUsers()
        );
    }

    public function testCanRetrieveAllEmailUsers()
    {
        $userService = new UserService($this->entityManager);
        $this->assertCount(
            3,
            $this->userRepository->getEmailUsers()
        );
    }

}
