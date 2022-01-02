<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

class SellAllWithLockPeriodStrategy extends SellStrategy
{
    public const NAME = 'sell.lock_period.all';

    // TODO parametrizar
    private const MINIMUN_RETURN = -1;

    // TODO decidir si el nº de candles y el timespan entran por parametro en el constructor.
    public function __construct() {
        parent::__construct(self::NAME);
    }


    public function getNumberOfCandles(): int
    {
        return 10;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        if (!$account->canSell()) {
            return null;
        }

        $base = $candles->getBase();
        if (!$account->hasBalanceOf($base)) {
            return null;
        }

        /** @var SpotBalance $base_balance */
        $base_balance = $account->getBalanceOf($base);
        $t_last_transaction = $base_balance->getLastTransactionTimestamp();
        $t_last_candle = $candles->getLastTimestamp();

        // TODO idea: llevar el tiempo de cooldown a una account::preference.
        // First 15 minutes after last trade, do nothing.
        if ($t_last_candle - $t_last_transaction < 15 * 60) {
            return null;
        }

        $candles = $this->curateData($candles);

        if ($candles->getPercentageReturn() > self::MINIMUN_RETURN                  // Performance still good
            &&                                                                      // and
            $candles->getLastPrice() > 0.98 * $base_balance->getAveragePrice()      // Price over 98% of avg. purchase price
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