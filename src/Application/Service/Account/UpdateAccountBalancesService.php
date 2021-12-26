<?php

namespace App\Application\Service\Account;

use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Account\SpotBalanceCollection;
use App\Domain\Model\Account\StakingBalance;
use App\Domain\Model\Account\StakingBalanceCollection;
use App\Domain\Model\Event\Event;
use App\Domain\Repository\Account\AccountRepository;
use App\Domain\Repository\Asset\SpotAssetRepository;
use App\Domain\Repository\Asset\StakingAssetRepository;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LengthException;

class UpdateAccountBalancesService
{
    private AccountRepository $account_repo;
    private SpotAssetRepository $spot_asset_repo;
    private StakingAssetRepository $staking_asset_repo;
    private KrakenApiClient $kraken_api_client;
    private EntityManagerInterface $entity_manager;


    public function __construct(
        AccountRepository $account_repo,
        SpotAssetRepository $spot_asset_repo,
        StakingAssetRepository $staking_asset_repo,
        KrakenApiClient $kraken_api_client,
        EntityManagerInterface $entity_manager
    ) {
        $this->account_repo = $account_repo;
        $this->spot_asset_repo = $spot_asset_repo;
        $this->staking_asset_repo = $staking_asset_repo;
        $this->kraken_api_client = $kraken_api_client;
        $this->entity_manager = $entity_manager;
    }

    public function execute(UpdateAccountBalancesRequest $request)
    {
        $account = $this->account_repo->findByReferenceOrFail($request->getReference());

        $this->kraken_api_client->configureKeys(...$account->getKeys());
        $kraken_response = $this->kraken_api_client->getAccountBalance();
        $response_balances = $this->cleanAssetSymbols($kraken_response['result']);

        // Create BalanceCollections with new balances
        $new_spot_balances = new SpotBalanceCollection();
        $new_staking_balances = new StakingBalanceCollection();
        foreach ($response_balances as $symbol => $balance_str) {
            $amount = (float)$balance_str;

            if ($this->isStakingAsset($symbol)) {
                $asset = $this->staking_asset_repo->findBySymbolOrFail($symbol);
                $new_staking_balances->add(StakingBalance::create($asset, $amount));
            }
            else {
                $asset = $this->spot_asset_repo->findBySymbolOrFail($symbol);
                $new_spot_balances->add(SpotBalance::create($asset, $amount));
            }
        }

        // Send new balances to Account, so it can add/create/delete them as considers.
        // Returning events is a dirty hack! Need to get Entities throw their own Events.
        $throwable_events_spot = $account->updateSpotBalances($new_spot_balances);
        $throwable_events_staking = $account->updateStakingBalances($new_staking_balances);
        $throwable_events = array_merge($throwable_events_spot, $throwable_events_staking);

        if (count($throwable_events) === 0) {
            return;
        }

        // Persist
        try {
            $this->entity_manager->beginTransaction();
            $this->entity_manager->persist($account);
            foreach ($throwable_events as $throwable_event) {
                $this->entity_manager->persist(Event::createFrom($throwable_event));
            }
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        } catch (Exception $e) {
            $this->entity_manager->rollBack();
            throw $e;
        }
    }

    private function isStakingAsset(string $symbol): bool
    {
        return count(explode('.', $symbol)) > 1;
    }

    private function cleanAssetSymbols(array $dirty_symbol_balances): array
    {
        $clean_symbol_balances = [];
        foreach ($dirty_symbol_balances as $dirty_symbol => $balance) {
            $clean_symbol_balances[$this->cleanAssetSymbol($dirty_symbol)] = $balance;
        }
        return $clean_symbol_balances;
    }

    private function cleanAssetSymbol(string $dirty_symbol): string
    {
        if ($dirty_symbol === '') {
            throw new LengthException("Asset symbol can't be empty.");
        }

        if (in_array($dirty_symbol[0], ['Z', 'X'], true) && strlen($dirty_symbol) === 4) {
            return substr($dirty_symbol, 1);
        }

        return $dirty_symbol;
    }
}