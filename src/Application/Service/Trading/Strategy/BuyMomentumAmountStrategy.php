<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class BuyMomentumAmountStrategy extends BuyStrategy
{
    public const NAME = 'buy.momentum.amount';


    // TODO parametrizar
    protected const MOMENTUM_RATIO = 3;
    private const MINIMUM_RETURN = 2;
    private const BUY_AMOUNT_QUOTE = 20;

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
        if ($candles->count() === 0) {
            return null;
        }
        if ($account->hasBalanceOf($candles->getBase())) {                  // already has a position
            return null;
        }

        // TODO Idea: getWeightedAverageClose!!!
        $average_momentum = $candles->getAverageClose() - $candles->getAverageOpen();
        $candles = $this->curateData($candles);
        $current_momentum = $candles->getAverageClose() - $candles->getAverageOpen();

        if ($current_momentum <= 0                                          // price going down
            ||                                                              // or
            $current_momentum < self::MOMENTUM_RATIO * $average_momentum    // low momentum ratio
            ||                                                              // or
            $candles->getPercentageReturn() < self::MINIMUM_RETURN          // price not going up enough
        ) {
            return null;
        }

        $quote_balance = $account->getSpotBalances()->findOneWithAssetSymbolOrFail($account->getQuoteSymbol());
        $quote_amount = min(self::BUY_AMOUNT_QUOTE, max(0, $quote_balance->getAmount() - $account->getSafetyAmount()));
        $price = $candles->getLastPrice();
        $base_amount =  $quote_amount / $price;
        return Order::createMarketBuy(
            $account,
            $candles->getPair(),
            $base_amount,
        );
    }

    public function curateData(CandleCollection $candles): CandleCollection
    {
        return $candles
            ->filterLastCandles($this->getNumberOfCandles());
    }

}