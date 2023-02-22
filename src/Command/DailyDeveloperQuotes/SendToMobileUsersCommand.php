<?php

declare(strict_types=1);

namespace App\Command\DailyDeveloperQuotes;

use App\Domain\User;
use App\Repository\QuoteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Rest\Client;

#[AsCommand(
    name: 'quotes:developer:mobile-users',
    description: 'Send daily developer quotes to mobile users.',
)]
class SendToMobileUsersCommand extends Command
{
    public function __construct(
        /**
         * @var array<int,User>
         */
        private readonly array           $users,
        private readonly QuoteRepository $quoteRepository,
        private readonly Client          $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to send daily developer quotes to mobile subscribers.');
    }

    public function execute(InputInterface  $input, OutputInterface $output): int
    {
        if (empty($this->users)) {
            $output->writeln("No mobile users to send quotes to.");
            return Command::SUCCESS;
        }

        $output->writeln("Sending quotes to mobile users");

        foreach ($this->users as $user) {
            $quote = $this->quoteRepository->getRandomQuoteForMobileUser($user);
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