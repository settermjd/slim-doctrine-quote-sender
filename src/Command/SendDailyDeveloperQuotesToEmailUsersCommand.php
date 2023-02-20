<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Quote;
use App\Domain\User;
use App\Repository\QuoteRepository;
use App\Service\QuoteService;
use App\Service\UserService;
use SendGrid\Mail\HtmlContent;
use SendGrid\Mail\Mail;
use SendGrid\Mail\Personalization;
use SendGrid\Mail\PlainTextContent;
use SendGrid\Mail\To;
use SendGrid\Mail\TypeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'daily-developer-quotes:email-users',
    description: 'Send daily developer quotes to email users.',
)]
class SendDailyDeveloperQuotesToEmailUsersCommand extends Command
{
    /**
     * @param array<int,User> $emailUsers
     */
    public function __construct(
        private readonly array $emailUsers,
        private readonly QuoteRepository $quoteService,
        private readonly \SendGrid $sendGrid
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to send daily developer quotes to email subscribers.');
    }

    /**
     * @throws TypeException
     */
    public function buildMailMessage(Mail $mail, Quote $quote, User $user): Mail
    {
        $mail->setFrom($_ENV['SEND_FROM_EMAIL_ADDRESS']);
        $mail->setReplyTo($_ENV['SEND_FROM_EMAIL_ADDRESS']);
        $mail->setSubject('Your Daily Developer Quote');
        $mail->addContent(
            new HtmlContent(
                sprintf(
                    '<p>%s</p>',
                    $quote->getPrintableQuote()
                )
            )
        );
        $mail->addContent(new PlainTextContent($quote->getPrintableQuote()));

        $personalisation = new Personalization();
        $personalisation->addTo(new To($user->getEmailAddress(), $user->getFullName()));
        $mail->addPersonalization($personalisation);

        return $mail;
    }

    public function execute(InputInterface  $input, OutputInterface $output): int
    {
        if (empty($this->emailUsers)) {
            $output->writeln("No email users to send quotes to.");
            return Command::SUCCESS;
        }

        $output->writeln("Sending quotes to email users");

        foreach ($this->emailUsers as $user) {
            $quote = $this->quoteService->getRandomQuoteForUser($user);
            $this->sendGrid->send($this->buildMailMessage(new Mail(), $quote, $user));

            $output->writeln(
                sprintf(
                    'Sending quote to email address: %s',
                    $user->getEmailAddress()
                )
            );
        }
        $output->writeln("Finished sending quotes to email users");

        return Command::SUCCESS;
    }
}