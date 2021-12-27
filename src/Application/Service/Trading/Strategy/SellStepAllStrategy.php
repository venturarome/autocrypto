<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;
use App\Domain\Model\Trading\OrderCollection;

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

    public function checkCanSell(Account $account): bool
    {
        return $account->getSpotBalances()->filterCrypto()->filterNonZero()->count() > 0;
    }

    public function run(Account $account, CandleCollection $candles): OrderCollection
    {
        $orders = new OrderCollection();

        $crypto_balances = $account->getSpotBalances()->filterCrypto();
        $owned_crypto_asset_symbols = $crypto_balances->getAssets()->getSymbolsArray();

        $base = $candles->getPair()->getBase();
        if ($candles->getPerformance()->getPercentageReturn() > self::MAXIMUM_RETURN
            || !in_array($base->getSymbol(), $owned_crypto_asset_symbols, true)
        ) {
            return $orders;
        }
        $base_amount = $crypto_balances->findOfAsset($base)->getAmount();
        $orders->add(Order::createMarketSell($account, $candles->getPair(), $base_amount));

        return $orders;
    }

//    public function curateData(CandleCollection $candles): CandleCollection
//    {
//        return $candles
//            ->fillGaps()
//            ->filterLastCandles($this->getNumberOfCandles());
//    }



}