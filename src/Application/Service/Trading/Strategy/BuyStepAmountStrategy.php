<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class BuyStepAmountStrategy extends BuyStrategy
{
    public const NAME = 'buy.step.amount';

    protected int $num_candles = 10;
    protected float $return_threshold = 4;
    protected float $amount = 20;

    public function __construct(array $custom_params = [])
    {
        $this->num_candles = $custom_params[Preference::NAME_BUY_NUM_CANDLES] ?? $this->num_candles;
        $this->return_threshold = $custom_params[Preference::NAME_BUY_RETURN_THRESHOLD] ?? $this->return_threshold;
        $this->amount = $custom_params[Preference::NAME_BUY_AMOUNT] ?? $this->amount;

        parent::__construct(self::NAME);
    }

    public function dumpConstants(): string
    {
        return "num_candles: " . $this->num_candles . PHP_EOL
            . "return_threshold: " . $this->return_threshold . PHP_EOL
            . "amount: " . $this->amount . PHP_EOL;
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

        $candles = $candles->filterLastCandles($this->num_candles);

        if ($candles->getPerformance()->getReturn() < $this->return_threshold) {   // poor performance
            return null;
        }

        $quote_balance = $account->getSpotBalances()->findOneWithAssetSymbolOrFail($account->getQuoteSymbol());
        $available_quote_amount = min($this->amount, max(0, $quote_balance->getAmount() - $account->getSafetyAmount()));
        $price = $candles->getLastPrice();
        $base_amount =  $available_quote_amount / $price;
        return Order::createMarketBuy(
            $account,
            $candles->getPair(),
            $base_amount,
        );
    }
}