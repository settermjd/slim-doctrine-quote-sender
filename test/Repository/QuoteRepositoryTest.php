<?php

namespace AppTest\Repository;

use App\Domain\Quote;
use App\Domain\User;
use App\Repository\QuoteRepository;
use App\Repository\UserRepository;
use App\Service\QuoteService;
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

class QuoteRepositoryTest extends TestCase
{
    private EntityManager|null $entityManager;
    private ORMPurger $purger;
    private QuoteRepository $quoteRepository;
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
        $this->quoteRepository = $this->entityManager->getRepository(Quote::class);
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

    public function testCanRetrieveRandomQuoteForUser()
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy([
            'emailAddress' => 'user3@example.org',
        ]);

        $this->assertInstanceOf(
            Quote::class,
            $this->quoteRepository->getRandomQuoteForUser($user)
        );
    }

    public function testCanRetrieveRandomQuoteForMobileUser()
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy([
            'mobileNumber' => '+14155552672',
        ]);

        $this->assertInstanceOf(
            Quote::class,
            $this->quoteRepository->getRandomQuoteForMobileUser($user)
        );
    }
}
