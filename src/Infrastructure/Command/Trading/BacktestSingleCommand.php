<?php

namespace App\Infrastructure\Command\Trading;

use App\Application\Service\Trading\Strategy\BuyStrategy;
use App\Application\Service\Trading\Strategy\SellStrategy;
use App\Application\Service\Trading\Strategy\StrategyFactory;
use App\Domain\Factory\Account\AccountFactory;
use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Account\PreferenceCollection;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Account\SpotBalanceCollection;
use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;
use App\Domain\Model\Trading\SpotTransaction;
use App\Domain\Repository\Asset\PairRepository;
use App\Domain\Repository\Asset\SpotAssetRepository;
use App\Domain\Repository\Trading\CandleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class BacktestSingleCommand extends Command
{
    private const INITIAL_AMOUNT = 100;
    private const MAX_RESULTS = 200;

    private SpotAssetRepository $spot_asset_repo;
    private CandleRepository $candle_repo;
    private PairRepository $pair_repo;
    private AccountFactory $account_factory;
    private StrategyFactory $strategy_factory;

    // Cache
    private OutputInterface $output;
    private string $base_asset_symbol;
    private string $quote_asset_symbol;
    private int $timespan;


    public function __construct(
        SpotAssetRepository $spot_asset_repo,
        PairRepository   $pair_repo,
        CandleRepository $candle_repo,
        AccountFactory $account_factory,
        StrategyFactory $strategy_factory,
    ) {
        $this->spot_asset_repo = $spot_asset_repo;
        $this->pair_repo = $pair_repo;
        $this->candle_repo = $candle_repo;
        $this->account_factory = $account_factory;
        $this->strategy_factory = $strategy_factory;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('autocrypto:backtest:single')
            ->setDescription("Backtest a trading strategy with a single pair")
            ->addArgument('buy_strategy_name', InputArgument::REQUIRED, 'Strategy to buy')
            ->addArgument('sell_strategy_name', InputArgument::REQUIRED, 'Strategy to sell')
            ->addArgument('base_asset_symbol', InputArgument::REQUIRED, 'Asset to buy-sell. Quote is EUR')
            ->addArgument('timespan', InputArgument::REQUIRED, 'Candle intervals')
            ->addArgument('date_from', InputArgument::REQUIRED, 'Start date. Format: Ymd. Time is assumed 00:00:00')
            ->addArgument('date_to', InputArgument::REQUIRED, 'End date. Format: Ymd. Time is assumed 23:59:59')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->output_section_date = $output->section();
        $this->output_section_order = $output->section();

        $this->output->writeln([
            'Backtesting Strategies',
            '======================',
        ]);

        $buy_strategy_name = $input->getArgument('buy_strategy_name');
        $sell_strategy_name = $input->getArgument('sell_strategy_name');
        $this->base_asset_symbol = $base_asset_symbol = $input->getArgument('base_asset_symbol');
        $this->quote_asset_symbol = $quote_asset_symbol = 'EUR';
        $this->timespan = $timespan = (int)$input->getArgument('timespan');
        $date_from = (new \DateTime($input->getArgument('date_from')))->setTime(0, 0);
        $date_to = (new \DateTime($input->getArgument('date_to')))->setTime(23, 59, 59);
        $base_asset = $this->spot_asset_repo->findBySymbolOrFail($base_asset_symbol);
        $quote_asset = $this->spot_asset_repo->findBySymbolOrFail($quote_asset_symbol);
        $pair = $this->pair_repo->findByAssetsOrFail($base_asset, $quote_asset);

        $account = $this->account_factory->create(...$this->fakeKeys());
        $account->updateBalances(new SpotBalanceCollection([SpotBalance::create($quote_asset, self::INITIAL_AMOUNT)]));
        $account->updatePreferences(new PreferenceCollection([
            new Preference(Preference::NAME_QUOTE_SYMBOL, 'EUR'),
            new Preference(Preference::NAME_SAFETY_AMOUNT, 10),
        ]));

        /** @var BuyStrategy $buy_strategy */
        /** @var SellStrategy $sell_strategy */
        $buy_strategy = $this->strategy_factory->createByName($buy_strategy_name);
        $sell_strategy = $this->strategy_factory->createByName($sell_strategy_name);


        $this->writeToFile([
            "Backtesting Strategies:",
            "  - Buy strategy: " . $buy_strategy_name,
            $buy_strategy->dumpConstants(),
            "  - Sell strategy: " . $sell_strategy_name,
            $sell_strategy->dumpConstants(),
            "",
            "Assets:",
            "  - Base: " . $base_asset_symbol,
            "  - Quote: " . $quote_asset_symbol,
            "",
            "Dates:",
            "  - From: " . $date_from->format('d-m-Y'),
            "  - To: " . $date_to->format('d-m-Y'),
            "",
            "",
            "[[Initial amount]] " . self::INITIAL_AMOUNT . " " . $quote_asset_symbol,
            "",
        ]);

        $first_result = 0;
        $min_num_candles = 720; // 12 h
        $year_month_str = $date_from->format('Ym');
        $candle_collection = new CandleCollection($pair, $timespan);
        $candle_block = $this->candle_repo->findForPairInRange($pair, $timespan, $date_from, $date_to, $first_result, self::MAX_RESULTS)->fillGaps();
        while (count($candle_block) > 0) {
            foreach ($candle_block as $candle) {
                /** @var Candle $candle */

                if (!$account->canTrade()) {
                    $date = date("Y-m-d H:i:s", $candle->getTimestamp());
                    $this->output->writeln(" On $date, you ran out of money!");
                    return Command::INVALID;
                }

                $candle_collection->add($candle);
                if ($candle_collection->count() <= $min_num_candles) {
                    continue;
                }
                $candle_collection->removeElement($candle_collection->first());

                $order = $buy_strategy->run($account, $candle_collection);
                if (!$order) {
                    $order = $sell_strategy->run($account, $candle_collection);
                }

                if ($order) {
                    $this->submitOrder($account, $order, $candle);
                }

                $current_year_month_str = (new \DateTime())->setTimestamp($candle->getTimestamp())->format('Ym');
                if ($current_year_month_str !== $year_month_str) {
                    $total_amount = $this->calculateTotalAmout($account, $base_asset, $quote_asset, $candle->getClose());

                    $total_return = 100 * ($total_amount/self::INITIAL_AMOUNT - 1);
                    $this->writeToFile([
                        "", " ====> Return at end of " . $year_month_str . ": " . $total_return, "",
                    ]);
                    $year_month_str = $current_year_month_str;
                }
            }

            $first_result += self::MAX_RESULTS - 1;
            $candle_block = $this->candle_repo->findForPairInRange($pair, $timespan, $date_from, $date_to, $first_result, self::MAX_RESULTS)->fillGaps();
            $candle_block->remove(0);
        }

        $final_amount = $this->calculateTotalAmout($account, $base_asset, $quote_asset, $candle->getClose());
        $this->writeToFile([
            "[[Final amount]] " . $final_amount,
            "[[Final return]] " . 100 * ($final_amount/self::INITIAL_AMOUNT - 1),
        ]);

        return Command::SUCCESS;
    }

    private function fakeKeys(): array
    {
        return [
            "00000000010000000002000000000300000000040000000005000000",
            "0000000001000000000200000000030000000004000000000500000000060000000007000000000800000000"
        ];
    }

    private function submitOrder(Account $account, ?Order $order, Candle $last_candle): void
    {
        if (!$order) {
            return;
        }

        // So far, we assume 'market' orders and only one Asset.
        $balances = $account->getSpotBalances();

        $base = $order->getBaseAsset();
        $quote = $order->getQuoteAsset();
        /** @var SpotBalance $base_balance */
        /** @var SpotBalance $quote_balance */
        $base_balance = $balances->findOfAsset($base);
        $quote_balance = $balances->findOfAsset($quote);
        $base_volume = $order->getVolume();
        $quote_volume = $base_volume * $last_candle->getClose();

        $date = date("Y-m-d H:i:s", $last_candle->getTimestamp());

        if ($order->isBuy()) {
            if ($quote_balance) {
                $quote_balance->subtract($quote_volume);
                $quote_transaction = new SpotTransaction(
                    'T_' . $last_candle->getTimestamp() . '_' . $order->getPairSymbol(),
                    $last_candle->getTimestamp(),
                    $order->getReference(),
                    $last_candle->getTimestamp(),
                    -$quote_volume,
                    0,
                    $quote_balance
                );
            }
            else {
                throw new \DomainException("Can't buy with Quote {$base->getSymbol()}, as it has no balance.");
            }

            if ($base_balance) {
                $base_balance->add($base_volume);
            }
            else {
                $base_balance = SpotBalance::create($base, $base_volume);
                $balances->add($base_balance);
            }
            $base_transaction = new SpotTransaction(
                'T_' . $last_candle->getTimestamp() . '_' . $order->getPairSymbol(),
                $last_candle->getTimestamp(),
                $order->getReference(),
                $last_candle->getTimestamp(),
                $base_volume,
                0,
                $base_balance
            );
            SpotTransaction::setPriceFromCounterparts($quote_transaction, $base_transaction);
            $quote_balance->addTransaction($quote_transaction);
            $base_balance->addTransaction($base_transaction);

            $message = "     [BUY] [$date] $quote_volume {$quote->getSymbol()} ==> $base_volume {$base->getSymbol()}";
            $this->output->writeln($message);
            $this->writeToFile([$message]);
        }
        else {  // is sell
            if ($base_balance) {
                $base_balance->subtract($base_volume);
                $base_transaction = new SpotTransaction(
                    'T_' . $last_candle->getTimestamp() . '_' . $order->getPairSymbol(),
                    $last_candle->getTimestamp(),
                    $order->getReference(),
                    $last_candle->getTimestamp(),
                    -$base_volume,
                    0,
                    $base_balance
                );
            }
            else {
                throw new \DomainException("Can't sell with Base {$base->getSymbol()}, as it has no balance.");
            }

            if ($quote_balance) {
                $quote_balance->add($quote_volume);
            }
            else {
                $quote_balance = SpotBalance::create($quote, $quote_volume);
                $balances->add($quote_balance);
            }
            $quote_transaction = new SpotTransaction(
                'T_' . $last_candle->getTimestamp() . '_' . $order->getPairSymbol(),
                $last_candle->getTimestamp(),
                $order->getReference(),
                $last_candle->getTimestamp(),
                $base_volume,
                0,
                $quote_balance
            );
            SpotTransaction::setPriceFromCounterparts($quote_transaction, $base_transaction);
            $base_balance->addTransaction($base_transaction);
            $quote_balance->addTransaction($quote_transaction);

            $message = "     [SELL] [$date] $base_volume {$base->getSymbol()} ==> $quote_volume {$quote->getSymbol()}";
            $this->output->writeln($message);
            $this->writeToFile([$message]);

            $amount_eur = $quote_balance->getAmount() + $last_candle->getClose() * $base_balance->getAmount();
            $total_return = 100 * ($amount_eur/self::INITIAL_AMOUNT - 1);
            $message = "       [SUMMARY] On $date, you have $amount_eur EUR ($total_return %)";
            $this->writeToFile([$message]);

        }
    }

    private function calculateTotalAmout(Account $account, SpotAsset $base, SpotAsset $quote, float $price): float
    {
        $balances = $account->getSpotBalances();
        $base_balance = $balances->findOfAsset($base);
        $quote_balance = $balances->findOfAsset($quote);
        $base_amount = $base_balance ? $base_balance->getAmount() : 0.0;
        $quote_amount = $quote_balance ? $quote_balance->getAmount() : 0.0;
        return $quote_amount + $price * $base_amount;
    }

    private function writeToFile(array $strs): void
    {
        $file_path = './data/results_'.$this->base_asset_symbol.$this->quote_asset_symbol.'_'.$this->timespan.'.txt';
        if (!($file = fopen($file_path, 'a'))) {
            throw new \Exception("Failed to open file '$file_path'");
        }
        foreach ($strs as $str) {
            fwrite($file, $str . PHP_EOL);
        }
        fclose($file);
    }
}