<?php

namespace AppTest\Command;

use App\Command\SendDailyDeveloperQuotesToEmailUsersCommand;
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
use SendGrid\Client;
use SendGrid\Mail\HtmlContent;
use SendGrid\Mail\Mail;
use SendGrid\Mail\Personalization;
use SendGrid\Mail\PlainTextContent;
use SendGrid\Mail\To;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendDailyDeveloperQuotesToEmailUsersCommandTest extends TestCase
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

    public function testCanSendDeveloperQuotesToEmailUsers()
    {
        $_ENV['SEND_FROM_EMAIL_ADDRESS'] = 'send.from@example.org';

        $emailAddress = 'user3@example.org';
        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['emailAddress' => $emailAddress]);

        /** @var Quote $quote */
        $quote = $this->entityManager
            ->getRepository(Quote::class)
            ->findOneBy([
                'quoteText' => "Any fool can write code that a computer can understand. Good programmers write code that humans can understand."
            ]);

        $mail = new Mail();
        $mail->setFrom($_ENV['SEND_FROM_EMAIL_ADDRESS']);
        $mail->setReplyTo($_ENV['SEND_FROM_EMAIL_ADDRESS']);
        $mail->setSubject('Your Daily Developer Quote');
        $mail->addContent(new HtmlContent(
            sprintf('<p>%s</p>', $quote->getPrintableQuote())
        ));
        $mail->addContent(new PlainTextContent($quote->getPrintableQuote()));

        $personalisation = new Personalization();
        $personalisation->addTo(new To($user->getEmailAddress(), $user->getFullName()));
        $mail->addPersonalization($personalisation);

        $sendGrid = $this->createMock(\SendGrid::class);
        $sendGrid
            ->expects($this->once())
            ->method('send')
            ->with($mail);

        $quoteRepository = $this->createMock(QuoteRepository::class);
        $quoteRepository
            ->expects($this->once())
            ->method('getRandomQuoteForUser')
            ->with($user)
            ->willReturn($quote);

        $command = new SendDailyDeveloperQuotesToEmailUsersCommand([$user], $quoteRepository, $sendGrid);

        $output = $this->createMock(OutputInterface::class);
        $output
            ->expects($this->exactly(3))
            ->method('writeln')
            ->willReturnOnConsecutiveCalls(
                "Sending quotes to email users",
                sprintf('Sending quote to email address: %s', $emailAddress),
                "Finished sending quotes to email users"
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
