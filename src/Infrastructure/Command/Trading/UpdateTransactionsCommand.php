<?php

namespace App\Infrastructure\Command\Trading;

use App\Application\Service\Trading\UpdateTransactionsRequest;
use App\Application\Service\Trading\UpdateTransactionsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateTransactionsCommand extends Command
{
    private UpdateTransactionsService $update_transactions_service;

    public function __construct(
        UpdateTransactionsService $update_transactions_service
    ) {
        $this->update_transactions_service = $update_transactions_service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:operations:update_transactions')
            ->setDescription("Retrieves info and populates Transactions (Spot and Staking) of one Account")
            ->addArgument('reference', InputArgument::REQUIRED, 'Account reference')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Updating Transactions',
            '=====================',
        ]);

        $reference = $input->getArgument('reference');
        try {
            $this->update_transactions_service->execute(new UpdateTransactionsRequest($reference));
        }
        catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

        return Command::SUCCESS;
    }
}