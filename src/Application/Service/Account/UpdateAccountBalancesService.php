<?php

namespace App\Application\Service\Account;

use App\Domain\Event\Account\AccountCreated;
use App\Domain\Factory\Account\AccountFactory;
use App\Domain\Model\Account\Account;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\Balance;
use App\Domain\Model\Asset\BalanceCollection;
use App\Domain\Model\Event\Event;
use App\Domain\Model\Shared\Amount\Amount;
use App\Domain\Repository\Account\AccountRepository;
use App\Domain\Repository\Asset\AssetRepository;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LengthException;

class UpdateAccountBalancesService
{
    private AccountRepository $account_repo;
    private AssetRepository $asset_repo;
    private KrakenApiClient $kraken_api_client;
    private EntityManagerInterface $entity_manager;


    public function __construct(
        AccountRepository $account_repo,
        AssetRepository $asset_repo,
        KrakenApiClient $kraken_api_client,
        EntityManagerInterface $entity_manager
    ) {
        $this->account_repo = $account_repo;
        $this->asset_repo = $asset_repo;
        $this->kraken_api_client = $kraken_api_client;
        $this->entity_manager = $entity_manager;
    }

    public function execute(UpdateAccountBalancesRequest $request)
    {
        $account = $this->account_repo->findByReferenceOrFail($request->getReference());

        $this->kraken_api_client->configureKeys(...$account->getKeys());
        $kraken_response = $this->kraken_api_client->getAccountBalance();
        $response_balances = $this->cleanAssetSymbols($kraken_response['result']);

        // Create BalanceCollection with new balances
        $new_balances = new BalanceCollection();
        foreach ($response_balances as $symbol => $balance_str) {
            if ($this->assetShouldBeExcluded($symbol)) { continue; }    // So far, I only want non-staked positions.
            $amount = Amount::fromString($balance_str);
            if ($amount->isZero()) {
                continue;
            }
            $asset = $this->asset_repo->findBySymbolOrFail($symbol);
            $new_balances->add(Balance::create($asset, $amount));
        }

        // Send new balances to Account, so it can add/create/delete them as considers.
        $throwable_events = $account->updateBalances($new_balances);  // Returning events is a dirty hack! Need to get Entities throw their own Events.

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

    private function assetShouldBeExcluded(string $symbol): bool
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