<?php

namespace App\Infrastructure\Command\Staking;

use App\Application\Service\Asset\CreateStakingAssetRequest;
use App\Application\Service\Asset\CreateStakingAssetService;
use App\Domain\Repository\Account\AccountRepository;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateStakingAssetsCommand extends Command
{
    private AccountRepository $account_repo;
    private KrakenApiClient $kraken_api_client;
    private CreateStakingAssetService $create_staking_asset_service;

    public function __construct(
        AccountRepository $account_repo,
        KrakenApiClient $kraken_api_client,
        CreateStakingAssetService $create_staking_asset_service
    ) {
        $this->account_repo = $account_repo;
        $this->kraken_api_client = $kraken_api_client;
        $this->create_staking_asset_service = $create_staking_asset_service;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:populate:staking_assets')
            ->setDescription("Retrieves info and populates StakingAssets")
            ->addArgument('reference', InputArgument::REQUIRED, 'Account reference')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Retrieving StakingAssets',
            '========================',
        ]);

        $reference = $input->getArgument('reference');
        $account = $this->account_repo->findByReferenceOrFail($reference);
        $this->kraken_api_client->configureKeys(...$account->getKeys());

        $kraken_response = $this->kraken_api_client->getStakeableAssets();
        foreach ($kraken_response['result'] as $staking_asset) {
            try {
                [$min_reward, $max_reward] = $this->getMinMaxRewards($staking_asset['rewards']['reward']);
                $this->create_staking_asset_service->execute(
                    new CreateStakingAssetRequest(
                        $staking_asset['staking_asset'],
                        $staking_asset['asset'],
                        $min_reward,
                        $max_reward,
                        $staking_asset['minimum_amount']['staking'],
                        $staking_asset['minimum_amount']['unstaking'],
                        $staking_asset['on_chain'],
                        $staking_asset['can_stake'],
                        $staking_asset['can_unstake'],
                        $staking_asset['method']
                    )
                );
            }
            catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    private function getMinMaxRewards(string $rewards): array
    {
        if (str_contains($rewards, '-')) {
            [$min_str, $max_str] = explode('-', $rewards);
            return [(float)$min_str, (float)$max_str];
        }
        return [(float)$rewards, (float)$rewards];
    }
}