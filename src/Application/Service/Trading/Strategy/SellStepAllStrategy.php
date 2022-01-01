<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

class SellStepAllStrategy extends SellStrategy
{
    public const NAME = 'sell.step.all';

    // TODO parametrizar
    private const MAXIMUM_RETURN = -0.2;

    // TODO decidir si el nº de candles y el timespan entran por parametro en el constructor.
    public function __construct() {
        parent::__construct(self::NAME);
    }


    public function getNumberOfCandles(): int
    {
        return 5;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        if (!$account->canSell()) {
            return null;
        }

        if ($candles->count() === 0) {
            return null;
        }
        $candles = $this->curateData($candles);

        $base = $candles->getBase();
        if (!$account->hasBalanceOf($base)) {
            return null;
        }
        $base_balance = $account->getBalanceOf($base);

        if ($candles->getPerformance()->getPercentageReturn() > self::MAXIMUM_RETURN) {
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