<?php

namespace App\Infrastructure\Command\Trading;

use App\Application\Service\Account\UpdateAccountBalancesRequest;
use App\Application\Service\Account\UpdateAccountBalancesService;
use App\Application\Service\Trading\Strategy\BuyStrategy;
use App\Application\Service\Trading\Strategy\SellStrategy;
use App\Application\Service\Trading\Strategy\StrategyFactory;
use App\Application\Service\Trading\UpdateTransactionsRequest;
use App\Application\Service\Trading\UpdateTransactionsService;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;
use App\Domain\Repository\Account\AccountRepository;
use App\Domain\Repository\Asset\PairRepository;
use App\Domain\Repository\Asset\SpotAssetRepository;
use App\Infrastructure\Provider\Kraken\KrakenApiClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AutoCryptoCommand extends Command
{
    private AccountRepository $account_repo;
    private StrategyFactory $strategy_factory;
    private SpotAssetRepository $spot_asset_repo;
    private UpdateAccountBalancesService $update_balances_service;
    private UpdateTransactionsService $update_transactions_service;
    private PairRepository $pair_repo;
    private KrakenApiClient $kraken_api_client;

    public function __construct(
        AccountRepository $account_repo,
        StrategyFactory $strategy_factory,
        SpotAssetRepository $spot_asset_repo,
        UpdateAccountBalancesService $update_balances_service,
        UpdateTransactionsService $update_transactions_service,
        PairRepository $pair_repo,
        KrakenApiClient $kraken_api_client,
    ) {
        $this->account_repo = $account_repo;
        $this->strategy_factory = $strategy_factory;
        $this->spot_asset_repo = $spot_asset_repo;
        $this->update_balances_service = $update_balances_service;
        $this->update_transactions_service = $update_transactions_service;
        $this->pair_repo = $pair_repo;
        $this->kraken_api_client = $kraken_api_client;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:trading:autocrypto')
            ->setDescription("Watches the market and buys/sells automatically.")
            ->addArgument('reference', InputArgument::REQUIRED, 'Account reference')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Autocrypto Core',
            '===============',
        ]);

        $reference = $input->getArgument('reference');
        $account = $this->account_repo->findByReferenceOrFail($reference);
        $this->kraken_api_client->configureKeys(...$account->getKeys());

        $this->update_balances_service->execute(new UpdateAccountBalancesRequest($reference));
        $this->update_transactions_service->execute(new UpdateTransactionsRequest($reference));

        /** @var BuyStrategy $buy_strategy */
        /** @var SellStrategy $sell_strategy */
        $buy_strategy = $this->strategy_factory->createByName($account->getBuyStrategyName());
        $sell_strategy = $this->strategy_factory->createByName($account->getSellStrategyName());

        $quote = $this->spot_asset_repo->findBySymbolOrFail($account->getQuoteSymbol());
        $pairs = $this->pair_repo->findByQuote($quote);

        while ($buy_strategy->checkCanBuy($account) || $sell_strategy->checkCanSell($account)) {
            foreach ($pairs as $pair) {
                /** @var Pair $pair */
                $candles = $this->getRealTimeCandles($pair, 1);

                $order = null;
                if ($buy_strategy->checkCanBuy($account)) {
                    $order = $buy_strategy->run($account, $candles);
                }
                else if ($sell_strategy->checkCanSell($account)) {
                    $order = $sell_strategy->run($account, $candles);
                }
                else {
                    // You ran out of money!
                    return Command::FAILURE;
                }

                if ($order && $account->canPlaceOrder($order, $candles->getLastPrice())) {
                    $this->placeOrder($order);
                    $this->update_balances_service->execute(new UpdateAccountBalancesRequest($reference));
                    $this->update_transactions_service->execute(new UpdateTransactionsRequest($reference));
                }
            }
        }

        return Command::SUCCESS;
    }

    private function getRealTimeCandles(Pair $pair, int $interval): CandleCollection
    {
        $pair_candles = $this->kraken_api_client->getOHLCData(['pair' => $pair->getSymbol(), 'interval' => $interval])['result'];
        unset($pair_candles['last']);
        $raw_candles = reset($pair_candles);
        return CandleCollection::createFromRawData($pair, $interval, $raw_candles);
    }

    private function placeOrder(Order $order, bool $wait_until_completed = true): void
    {
        $timestamp = floor(microtime(true));
        $add_order_response = $this->kraken_api_client->addOrder([
            'ordertype' => $order->getType(),
            'type' => $order->getOperation(),
            'pair' => $order->getPairSymbol(),
        ]);
        $order_id = $add_order_response['result']['txid'];

        if (!$wait_until_completed) {
            return;
        }

        $closed = false;
        while (!$closed){
            usleep(250000);
            $closed_orders_response = $this->kraken_api_client->getClosedOrders(['start' => $timestamp]);
            $order_raw = $closed_orders_response['result']['closed'][(string)$order_id];
            $closed = $order_raw['status'] === Order::STATUS_CLOSED;
        }
    }
}