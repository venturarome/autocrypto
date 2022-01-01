<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class BuyStepAmountStrategy extends BuyStrategy
{
    public const NAME = 'buy.step.amount';


    // TODO parametrizar
    private const MINIMUM_RETURN = 4;
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
        if (!$account->canBuy()) {
            return null;
        }

        if($candles->count() === 0) {
            return null;
        }
        if ($account->hasBalanceOf($candles->getBase())) {                      // already has a position
            return null;
        }

        $candles = $this->curateData($candles);

        if ($candles->getPerformance()->getReturn() < self::MINIMUM_RETURN) {   // poor performance
            return null;
        }

        $quote_balance = $account->getSpotBalances()->findOneWithAssetSymbolOrFail($account->getQuoteSymbol());
        $available_quote_amount = min(self::BUY_AMOUNT_QUOTE, max(0, $quote_balance->getAmount() - $account->getSafetyAmount()));
        $price = $candles->getLastPrice();
        $base_amount =  $available_quote_amount / $price;
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