<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

class SellNoLossesAllStrategy extends SellStrategy
{
    public const NAME = 'sell.no_losses.all';

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

    public function checkCanSell(Account $account): bool
    {
        return $account->getSpotBalances()->filterCrypto()->filterNonZero()->count() > 0;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        $candles = $this->curateData($candles);

        $base = $candles->getBase();
        if (!$account->hasBalanceOf($base)) {
            return null;
        }
        /** @var SpotBalance $base_balance */
        $base_balance = $account->getBalanceOf($base);

        if ($candles->getPerformance()->getPercentageReturn() > self::MAXIMUM_RETURN    // Performance still good AND
            &&                                                                          // and
            $candles->getLastPrice() > 0.97 * $base_balance->getAveragePrice()          // Price over 97% of avg. purchase price
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