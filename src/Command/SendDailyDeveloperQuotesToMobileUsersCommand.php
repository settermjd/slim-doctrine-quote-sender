<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\QuoteService;
use App\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Rest\Client;

class SendDailyDeveloperQuotesToMobileUsersCommand extends Command
{
    protected static $defaultDescription = 'Send daily developer quotes.';

    public function __construct(
        private readonly UserService $userService,
        private readonly QuoteService $quoteService,
        private readonly Client $client
    ) {
        parent::__construct();
    }

    public function execute(InputInterface  $input, OutputInterface $output): int
    {
        $users = $this->userService->getMobileUsers();

        foreach ($users as $user) {
            $quote = $this->quoteService
                        ->getRandomQuoteForUser($user);
            $this->client->messages->create(
                $user->getMobileNumber(),
                [
                    'from' => $_ENV['TWILIO_PHONE_NUMBER'],
                    'body' => $quote->getPrintableQuote()
                ]
            );

            $output->writeln(
                sprintf(
                    'Sending quote to mobile number: %s',
                    $user->getMobileNumber()
                )
            );
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to send daily developer quotes.');
        $this->addArgument(
            'user_type',
            InputArgument::REQUIRED,
            'The user type to send quotes to'
        );
    }
}