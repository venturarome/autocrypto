<?php

namespace App\Infrastructure\Command\Asset;

use App\Application\Service\Asset\CreateSpotAssetRequest;
use App\Application\Service\Asset\CreateSpotAssetService;
use App\Infrastructure\Provider\Kaiko\KaikoApiClient;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateAssetsCommand extends Command
{
    private KrakenApiClient $kraken_api_client;
    private KaikoApiClient $kaiko_api_client;
    private CreateSpotAssetService $create_asset_service;

    public function __construct(
        KrakenApiClient $kraken_api_client,
        KaikoApiClient $kaiko_api_client,
        CreateSpotAssetService $create_asset_service
    ) {
        $this->kraken_api_client = $kraken_api_client;
        $this->kaiko_api_client = $kaiko_api_client;
        $this->create_asset_service = $create_asset_service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:populate:assets')
            ->setDescription("Retrieves info and populates Assets")
            ->addOption('symbols', 's', InputOption::VALUE_OPTIONAL, 'Comma-separated Asset symbols. Empty for all.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Retrieving Assets',
            '=================',
        ]);

        $data = [];

        $symbols = $input->getOption('symbols');
        if ($symbols) {
            $data['asset'] = $symbols;
        }

        $kraken_response = $this->kraken_api_client->getAssetInfo($data);
        $kaiko_response = $this->kaiko_api_client->getAssets();
        $kaiko_data = $kaiko_response['data'];

        foreach ($kraken_response['result'] as $ext_symbol => $asset) {
            $symbol = $asset['altname'];
            $key = array_search(strtolower($symbol), array_column($kaiko_data, "code"), true);
            $name = $key ? $kaiko_data[$key]["name"] : null;
            try {
                $this->create_asset_service->execute(
                    new CreateSpotAssetRequest(
                        $symbol,
                        $name,
                        $asset['decimals'],
                        $asset['display_decimals'],
                        $ext_symbol
                    )
                );
            }
            catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}