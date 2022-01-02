<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class SellMomentumAllStrategy extends BuyStrategy
{
    public const NAME = 'sell.momentum.all';

    // TODO parametrizar
    protected const MOMENTUM_RATIO = 3;
    private const RETURN_THRESHOLD = -1;   // should be called return_threshold??

    // TODO decidir si el nº de candles y el timespan entran por parametro en el constructor.
    public function __construct() {
        parent::__construct(self::NAME);
    }

    public static function dumpConstants(): string
    {
        return "MOMENTUM_RATIO: " . self::MOMENTUM_RATIO . PHP_EOL
            . "RETURN_THRESHOLD: " . self::RETURN_THRESHOLD . PHP_EOL;
    }

    public function getNumberOfCandles(): int
    {
        return 5;
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
        $candles = $this->curateData($candles);
        $current_momentum = $candles->getAverageClose() - $candles->getAverageOpen();

        if ($current_momentum > 0                                           // price going up
            ||                                                              // or
            self::MOMENTUM_RATIO * $current_momentum > $average_momentum    // high momentum ratio  TODO test
            ||                                                              // or
            $candles->getPercentageReturn() > self::RETURN_THRESHOLD        // price not going down enough
        ) {
            return null;
        }

        return Order::createMarketSell($account, $candles->getPair(), $base_balance->getAmount());
    }

    public function curateData(CandleCollection $candles): CandleCollection
    {
        return $candles
            ->filterLastCandles($this->getNumberOfCandles());
    }

}