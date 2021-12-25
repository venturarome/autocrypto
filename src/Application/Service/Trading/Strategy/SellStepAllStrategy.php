<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\CandleMap;
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

//    public function getCandlesTimespan(): int
//    {
//        return 5;
//    }

    public function checkCanSell(Account $account): bool
    {
        return $account->getSpotBalances()->filterCrypto()->filterNonZero()->count() > 0;
    }

    public function run(Account $account, CandleMap $candle_map): OrderCollection
    {
        $orders = new OrderCollection();

        $crypto_balances = $account->getSpotBalances()->filterCrypto();
        $owned_crypto_asset_symbols = $crypto_balances->getAssets()->getSymbolsArray();

        //$candle_map = $this->curateData($candle_map);
        foreach ($candle_map as $candle_collection) {
            /** @var CandleCollection $candle_collection */
            $base = $candle_collection->getPair()->getBase();
            if ($candle_collection->getPerformance()->getPercentageReturn() > self::MAXIMUM_RETURN
                || !in_array($base->getSymbol(), $owned_crypto_asset_symbols, true)
            ) {
                continue;
            }
            $base_amount = $crypto_balances->findOf($base)->getAmount();
            $orders->add(Order::createMarketSell($account, $candle_collection->getPair(), $base_amount));
        }

        return $orders;
    }

//    public function curateData(CandleMap $candle_map): CandleMap
//    {
//        return $candle_map
//            ->fillGaps()
//            // ->increaseTimespan($this->getCandlesTimespan())
//            ->filterLastCandles($this->getNumberOfCandles());
//    }

    public function getCandlesTimespan(): int
    {
        // TODO: Implement getCandlesTimespan() method.
    }


}