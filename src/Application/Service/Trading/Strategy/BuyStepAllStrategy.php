<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

class BuyStepAllStrategy extends BuyStrategy
{
    public const NAME = 'buy.step.all';

    protected int $num_candles = 10;
    protected float $return_threshold = 3;

    public function __construct(array $custom_params = []) {
        $this->num_candles = $custom_params[Preference::NAME_BUY_NUM_CANDLES] ?? $this->num_candles;
        $this->return_threshold = $custom_params[Preference::NAME_BUY_RETURN_THRESHOLD] ?? $this->return_threshold;

        parent::__construct(self::NAME);
    }

    public function dumpConstants(): string
    {
        return "num_candles: " . $this->num_candles . PHP_EOL
            . "return_threshold: " . $this->return_threshold . PHP_EOL;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        if (!$account->canBuy()) {
            return null;
        }

        if($candles->count() === 0) {
            return null;
        }
        $candles = $candles->filterLastCandles($this->num_candles);

        $performance = $candles->getPerformance();
        if ($performance->getPercentageReturn() <= $this->return_threshold) {
            return null;
        }

        $quote_balance = $account->getSpotBalances()->findOneWithAssetSymbolOrFail($account->getQuoteSymbol());
        $available_quote_amount = $quote_balance->getAmount() - $account->getSafetyAmount();
        $price = $candles->getLastPrice();
        $base_amount = $available_quote_amount / $price;
        return Order::createMarketBuy(
            $account,
            $candles->getPair(),
            $base_amount,
        );
    }
}