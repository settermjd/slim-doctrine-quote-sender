<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Quote;
use App\Domain\User;
use App\Service\QuoteService;
use App\Service\UserService;
use SendGrid\Mail\HtmlContent;
use SendGrid\Mail\Mail;
use SendGrid\Mail\Personalization;
use SendGrid\Mail\PlainTextContent;
use SendGrid\Mail\To;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendDailyDeveloperQuotesToEmailUsersCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
        private readonly QuoteService $quoteService,
        private readonly \SendGrid $sendGrid
    )
    {
        parent::__construct();
    }

    /**
     * @throws \SendGrid\Mail\TypeException
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

    protected function configure(): void
    {
        $this->setHelp('This command allows you to send daily developer quotes to email subscribers.');
    }

    public function execute(InputInterface  $input, OutputInterface $output): int
    {
        $users = $this->userService->getEmailUsers();

        foreach ($users as $user) {
            $quote = $this->quoteService->getRandomQuoteForUser($user);
            $this->sendGrid->send($this->buildMailMessage(new Mail(), $quote, $user));

            $output->writeln(
                sprintf(
                    'Sending quote to email address: %s',
                    $user->getEmailAddress()
                )
            );
        }

        return Command::SUCCESS;
    }
}