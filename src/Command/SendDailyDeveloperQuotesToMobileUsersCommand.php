<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\QuoteService;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Rest\Client;

#[AsCommand(
    name: 'daily-developer-quotes:mobile-users',
    description: 'Send daily developer quotes to mobile users.',
)]
class SendDailyDeveloperQuotesToMobileUsersCommand extends Command
{
    public function __construct(
        private readonly UserService $userService,
        private readonly QuoteService $quoteService,
        private readonly Client $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to send daily developer quotes to mobile subscribers.');
    }

    public function execute(InputInterface  $input, OutputInterface $output): int
    {
        $users = $this->userService->getMobileUsers();
        if (empty($users)) {
            $output->writeln("No mobile users to send quotes to.");
            return Command::SUCCESS;
        }

        $output->writeln("Sending quotes to mobile users");

        foreach ($users as $user) {
            $quote = $this->quoteService
                        ->getRandomQuoteForMobileUser($user);
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

        $output->writeln("Finished sending quotes to mobile users");

        return Command::SUCCESS;
    }
}