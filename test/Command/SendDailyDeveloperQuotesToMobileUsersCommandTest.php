<?php

namespace AppTest\Command;

use App\Command\SendDailyDeveloperQuotesToMobileUsersCommand;
use App\Domain\Quote;
use App\Domain\User;
use App\Repository\QuoteRepository;
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
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

class SendDailyDeveloperQuotesToMobileUsersCommandTest extends TestCase
{
    private EntityManager $entityManager;
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

    public function testCanSendDeveloperQuotesToMobileUsers()
    {
        $twilioNumber = '+15017122661';

        $mobileNumber = '+14155552672';
        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['mobileNumber' => $mobileNumber]);

        /** @var Quote $quote */
        $quote = $this->entityManager
            ->getRepository(Quote::class)
            ->findOneBy([
                'quoteText' => "Any fool can write code that a computer can understand. Good programmers write code that humans can understand."
            ]);

        $messages = $this->createMock(MessageList::class);
        $messages
            ->expects($this->once())
            ->method('create')
            ->with(
                $user->getMobileNumber(),
                [
                    'from' => $twilioNumber,
                    'body' => sprintf(
                        '%s - %s',
                        $quote->getQuoteText(),
                        $quote->getQuoteAuthor()->getFullName()
                    )
                ]
            );

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('__get')
            ->with('messages')
            ->willReturn($messages);

        $_ENV['TWILIO_PHONE_NUMBER'] = $twilioNumber;

        $quoteRepository = $this->createMock(QuoteRepository::class);
        $quoteRepository
            ->expects($this->once())
            ->method('getRandomQuoteForMobileUser')
            ->with($user)
            ->willReturn($quote);

        $command = new SendDailyDeveloperQuotesToMobileUsersCommand([$user], $quoteRepository, $client);

        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects($this->exactly(3))
            ->method('writeln')
            ->willReturnOnConsecutiveCalls(
                "Sending quotes to mobile users",
                sprintf('Sending quote to mobile number: %s', $mobileNumber),
                "Finished sending quotes to mobile users"
            );

        $this->assertSame(
            Command::SUCCESS,
            $command->execute(
                $this->createMock(InputInterface::class),
                $output
            )
        );
    }
}
