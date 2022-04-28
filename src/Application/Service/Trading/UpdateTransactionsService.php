<?php

namespace App\Application\Service\Trading;

use App\Domain\Event\Trading\TransactionAdded;
use App\Domain\Model\Event\Event;
use App\Domain\Model\Trading\SpotTransaction;
use App\Domain\Model\Trading\StakingTransaction;
use App\Domain\Repository\Account\AccountRepository;
use App\Domain\Repository\Asset\SpotAssetRepository;
use App\Domain\Repository\Asset\StakingAssetRepository;
use App\Domain\Repository\Trading\SpotTransactionRepository;
use App\Domain\Repository\Trading\StakingTransactionRepository;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LengthException;

class UpdateTransactionsService
{
    private AccountRepository $account_repo;
    private KrakenApiClient $kraken_api_client;
    private SpotTransactionRepository $spot_transaction_repo;
    private StakingTransactionRepository $staking_transaction_repo;
    private SpotAssetRepository $spot_asset_repo;
    private StakingAssetRepository $staking_asset_repo;
    private EntityManagerInterface $entity_manager;

    public function __construct(
        AccountRepository $account_repo,
        KrakenApiClient $kraken_api_client,
        SpotTransactionRepository $spot_transaction_repo,
        StakingTransactionRepository $staking_transaction_repo,
        SpotAssetRepository $spot_asset_repo,
        StakingAssetRepository $staking_asset_repo,
        EntityManagerInterface $entity_manager
    ) {
        $this->account_repo = $account_repo;
        $this->spot_transaction_repo = $spot_transaction_repo;
        $this->staking_transaction_repo = $staking_transaction_repo;
        $this->kraken_api_client = $kraken_api_client;
        $this->spot_asset_repo = $spot_asset_repo;
        $this->staking_asset_repo = $staking_asset_repo;
        $this->entity_manager = $entity_manager;
    }


    public function execute(UpdateTransactionsRequest $request)
    {
        $account = $this->account_repo->findByReferenceOrFail($request->getReference());

        $spot_balances = $account->getSpotBalances();
        $staking_balances = $account->getStakingBalances();

        $this->kraken_api_client->configureKeys(...$account->getKeys());
        $kraken_response = $this->kraken_api_client->getLedgersInfo();
        $response_transactions = $kraken_response['result']['ledger'];

        // TODO start temporary code
        if (count($response_transactions) >= 50) {
            throw new Exception("Number of transactions is 50, which is the limit of the WS! "
                . "There may be more than 50. Please, debug and change the code to see how to get all transactions! "
                . "Maybe, using the 'start' and 'end' parameters in the WS!");
        }
        // TODO end temporary code

        uasort($response_transactions, static function ($tx1, $tx2) {
            return $tx1['time'] <=> $tx2['time'];
        });

        foreach ($response_transactions as $tx_reference => $raw_tx) {
            $asset_symbol = $this->cleanAssetSymbol($raw_tx['asset']);

            if ($this->isStakingAsset($asset_symbol)) {
                if ($this->staking_transaction_repo->findByReference($tx_reference)) {
                    continue;
                }
                $asset = $this->staking_asset_repo->findBySymbolOrFail($asset_symbol);
                $balance = $staking_balances->findOfAssetOrFail($asset);
                $transaction = new StakingTransaction(
                    $tx_reference,
                    $raw_tx['type'],
                    $raw_tx['refid'],
                    $raw_tx['time'],
                    $raw_tx['amount'],
                    $raw_tx['fee'],
                    $balance
                );
            }
            else {
                if ($this->spot_transaction_repo->findByReference($tx_reference)) {
                    continue;
                }
                $asset = $this->spot_asset_repo->findBySymbolOrFail($asset_symbol);
                $balance = $spot_balances->findOfAssetOrFail($asset);
                $transaction = new SpotTransaction(
                    $tx_reference,
                    $raw_tx['type'],
                    $raw_tx['refid'],
                    $raw_tx['time'],
                    $raw_tx['amount'],
                    $raw_tx['fee'],
                    $balance
                );

                // Get purchase price.
                if ($transaction->isTrade()) {
                    $sibling_txs = $this->spot_transaction_repo->findByOperationReference($transaction->getOperationReference());
                    $counterpart_txs = $sibling_txs->filterOutOfAssetSymbol($transaction->getAssetSymbol());

                    // TODO start temporary code. In case one trade is completed with several prices.
                    if ($counterpart_txs->count() > 1) {
                        throw new \Exception("Several counterpart Transactions found for Transaction with reference '{$transaction->getReference()}'. "
                            . "Please, debug and develop code to handle this situation!");
                    }
                    // TODO end temporary code.

                    if ($counterpart_txs->count() === 1) {
                        /** @var SpotTransaction $counterpart_tx */
                        $counterpart_tx = $counterpart_txs->first();
                        SpotTransaction::setPriceFromCounterparts($transaction, $counterpart_tx);
                    }

                }

            }
            $event = Event::createFrom(TransactionAdded::raise($transaction));

            // Persist
            try {
                $this->entity_manager->beginTransaction();
                $this->entity_manager->persist($transaction);
                if (isset($counterpart_tx)) {
                    $this->entity_manager->persist($counterpart_tx);
                    unset($counterpart_tx);
                }
                $this->entity_manager->persist($event);
                $this->entity_manager->flush();
                $this->entity_manager->commit();
            } catch (Exception $e) {
                $this->entity_manager->rollBack();
                throw $e;
            }
        }

    }

    private function isStakingAsset(string $symbol): bool
    {
        return count(explode('.', $symbol)) > 1;
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