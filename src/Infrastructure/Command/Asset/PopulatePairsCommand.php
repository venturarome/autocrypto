<?php

namespace App\Infrastructure\Command\Asset;

use App\Application\Service\Asset\CreateAssetRequest;
use App\Application\Service\Asset\CreateAssetService;
use App\Application\Service\Asset\CreatePairRequest;
use App\Application\Service\Asset\CreatePairService;
use App\Infrastructure\Provider\Kaiko\KaikoApiClient;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulatePairsCommand extends Command
{
    private KrakenApiClient $kraken_api_client;
//    private KaikoApiClient $kaiko_api_client;
    private CreatePairService $create_pair_service;

    public function __construct(
        KrakenApiClient $kraken_api_client,
//        KaikoApiClient $kaiko_api_client,
        CreatePairService $create_pair_service
    ) {
        $this->kraken_api_client = $kraken_api_client;
//        $this->kaiko_api_client = $kaiko_api_client;
        $this->create_pair_service = $create_pair_service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:populate:pairs')
            ->setDescription("Retrieves info and populates Pairs")
            ->addOption('symbols', 's', InputOption::VALUE_OPTIONAL, 'Comma-separated Pair symbols. Empty for all.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Retrieving Pairs',
            '================',
        ]);

        $ok_count = $ko_count = 0;
        $data = [];

        $symbols = $input->getOption('symbols');
        if ($symbols) {
            $data['pair'] = $symbols;
        }

        $kraken_response = $this->kraken_api_client->getTradableAssetPairs($data);

        foreach ($kraken_response['result'] as $pair) {
            $symbol = $pair['altname'];
            [$base, $quote] = $this->extractBaseAndQuote($pair);
            try {
                $this->create_pair_service->execute(
                    new CreatePairRequest(
                        $symbol,
                        $base,
                        $quote,
                        $pair['pair_decimals'],
                        $pair['lot_decimals'],
                        $pair['ordermin'],
                        $pair['leverage_buy'],
                        $pair['leverage_sell']
                    )
                );
                $ok_count++;
            }
            catch (Exception $e) {
                $output->writeln($e->getMessage());
                $ko_count++;
            }
        }

        $output->writeln([
            "--> Summary:",
            "     --> OKs: $ok_count",
            "     --> KOs: $ko_count",
        ]);

        return Command::SUCCESS;
    }

    private function extractBaseAndQuote(array $pair): array
    {
        $wsname = $pair['wsname'];  // what if it is not defined??

        return explode('/', $wsname);
    }
}