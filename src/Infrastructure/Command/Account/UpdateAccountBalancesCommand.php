<?php

namespace App\Infrastructure\Command\Account;

use App\Application\Service\Account\UpdateAccountBalancesRequest;
use App\Application\Service\Account\UpdateAccountBalancesService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateAccountBalancesCommand extends Command
{
    private UpdateAccountBalancesService $update_account_balances_service;

    public function __construct(
        UpdateAccountBalancesService $update_account_balances_service
    ) {
        $this->update_account_balances_service = $update_account_balances_service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:operations:update_account_balances')
            ->setDescription("Updates asset balances on an account")
            ->addArgument('reference', InputArgument::REQUIRED, 'Account reference')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Updating Account Balances',
            '=========================',
        ]);

        $reference = $input->getArgument('reference');
        try {
            $this->update_account_balances_service->execute(new UpdateAccountBalancesRequest($reference));
        }
        catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        return Command::SUCCESS;
    }
}