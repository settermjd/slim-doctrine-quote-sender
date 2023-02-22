<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Domain\Quote;
use App\Domain\QuoteType;
use App\Domain\User;
use App\Repository\UserRepository;
use App\Service\QuoteService;
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

class QuoteServiceTest extends TestCase
{
    private EntityManager|null $entityManager;
    private ORMPurger $purger;

    public function setUp(): void
    {
        /** @var \Psr\Container\ContainerInterface $container */
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

    public function testCanMarkQuoteAsHavingBeingSentToUser()
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy([
            'emailAddress' => 'user3@example.org',
        ]);

        /** @var Quote $quote */
        $quote = $this->entityManager
            ->getRepository(Quote::class)
            ->findOneBy(
                [
                    'quoteText' => "Don't comment bad code - rewrite it."
                ]
            );

        $result = (new QuoteService($this->entityManager))
            ->markQuoteAsSentToUser($user, $quote);
        $this->assertTrue($result);

        $userService = new UserService($this->entityManager);
        $quotes = $userRepository->getQuotes($user, QuoteType::Viewed);
        $this->assertCount(2, $quotes);
        $this->assertTrue($quotes->contains($quote));
    }
}
