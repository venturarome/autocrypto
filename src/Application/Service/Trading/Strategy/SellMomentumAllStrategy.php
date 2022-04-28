<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class SellMomentumAllStrategy extends SellStrategy
{
    public const NAME = 'sell.momentum.all';

    protected int $num_candles = 10;
    protected float $momentum_ratio = 3;
    protected float $return_threshold = -1;

    public function __construct(array $custom_params = []) {
        $this->num_candles = $custom_params[Preference::NAME_SELL_NUM_CANDLES] ?? $this->num_candles;
        $this->momentum_ratio = $custom_params[Preference::NAME_SELL_MOMENTUM_RATIO] ?? $this->momentum_ratio;
        $this->return_threshold = $custom_params[Preference::NAME_SELL_RETURN_THRESHOLD] ?? $this->return_threshold;

        parent::__construct(self::NAME);
    }

    public function dumpConstants(): string
    {
        return "num_candles: " . $this->num_candles . PHP_EOL
            . "momentum_ratio: " . $this->momentum_ratio . PHP_EOL
            . "return_threshold: " . $this->return_threshold . PHP_EOL;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        if ($candles->count() === 0) {
            return null;
        }

        $base_balance = $account->getBalanceOf($candles->getBase());
        if (!$base_balance) {                  // already has a position
            return null;
        }

        // TODO Idea: getWeightedAverageClose!!!
        $average_momentum = $candles->getAverageClose() - $candles->getAverageOpen();
        $candles = $candles->filterLastCandles($this->num_candles);
        $current_momentum = $candles->getAverageClose() - $candles->getAverageOpen();

        if ($current_momentum > 0                                           // price going up
            ||                                                              // or
            $this->momentum_ratio * $current_momentum > $average_momentum   // high momentum ratio  TODO test
            ||                                                              // or
            $candles->getPercentageReturn() > $this->return_threshold       // price not going down enough
        ) {
            return null;
        }

        return Order::createMarketSell($account, $candles->getPair(), $base_balance->getAmount());
    }
}