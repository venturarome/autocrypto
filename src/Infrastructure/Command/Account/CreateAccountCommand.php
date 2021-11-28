<?php

namespace App\Infrastructure\Command\Account;

use App\Application\Service\Account\CreateAccountRequest;
use App\Application\Service\Account\CreateAccountService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAccountCommand extends Command
{
    private CreateAccountService $create_account_service;

    public function __construct(CreateAccountService $create_account_service)
    {
        $this->create_account_service = $create_account_service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:operations:create_account')
            ->setDescription("Creates an account, providing Kraken keys")
            ->addArgument('api_key', InputArgument::REQUIRED, 'Kraken API key')
            ->addArgument('secret_key', InputArgument::REQUIRED, 'Kraken secret key')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Account Creator',
            '===============',
            '',
        ]);

        $api_key = $input->getArgument('api_key');
        $secret_key = $input->getArgument('secret_key');

        try {
            $this->create_account_service->execute(new CreateAccountRequest($api_key, $secret_key));
        }
        catch (Exception $e) {
            $output->writeln($e->getMessage());
        }

        return Command::SUCCESS;
    }
}