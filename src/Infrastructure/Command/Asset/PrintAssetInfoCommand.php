<?php

namespace App\Infrastructure\Command\Asset;

use App\Application\Service\Account\CreateAccountRequest;
use App\Application\Service\Account\CreateAccountService;
use App\Application\Service\Asset\GetAssetInfo;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PrintAssetInfoCommand extends Command
{
    private GetAssetInfo $get_asset_info;

    public function __construct(GetAssetInfo $get_asset_info)
    {
        $this->get_asset_info = $get_asset_info;
        parent::__construct();
    }

    protected function configure(): void
    {
        // TODO seguir aquí.
        $this
            ->setName('autocrypto:scraping:asset_info')
            ->setDescription("Gets info from an Asset")
            ->addArgument('asset_symbol', InputOption::VALUE_OPTIONAL, 'Asset symbol')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}