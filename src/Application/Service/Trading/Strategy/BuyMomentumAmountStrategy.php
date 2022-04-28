<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class BuyMomentumAmountStrategy extends BuyStrategy
{
    public const NAME = 'buy.momentum.amount';

    protected int $num_candles = 10;
    protected float $momentum_ratio = 3;
    protected float $return_threshold = 2;
    protected float $amount = 20;

    public function __construct(array $custom_params = []) {
        $this->num_candles = $custom_params[Preference::NAME_BUY_NUM_CANDLES] ?? $this->num_candles;
        $this->momentum_ratio = $custom_params[Preference::NAME_BUY_MOMENTUM_RATIO] ?? $this->momentum_ratio;
        $this->return_threshold = $custom_params[Preference::NAME_BUY_RETURN_THRESHOLD] ?? $this->return_threshold;
        $this->amount = $custom_params[Preference::NAME_BUY_AMOUNT] ?? $this->amount;

        parent::__construct(self::NAME);
    }

    public function dumpConstants(): string
    {
        return "num_candles: " . $this->num_candles . PHP_EOL
            . "momentum_ratio: " . $this->momentum_ratio . PHP_EOL
            . "return_threshold: " . $this->return_threshold . PHP_EOL
            . "amount: " . $this->amount . PHP_EOL;
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
        $candles = $candles->filterLastCandles($this->num_candles);
        $current_momentum = $candles->getAverageClose() - $candles->getAverageOpen();

        if ($current_momentum <= 0                                          // price going down
            ||                                                              // or
            $current_momentum < $this->momentum_ratio * $average_momentum   // low momentum ratio
            ||                                                              // or
            $candles->getPercentageReturn() < $this->return_threshold       // price not going up enough
        ) {
            return null;
        }

        $quote_balance = $account->getSpotBalances()->findOneWithAssetSymbolOrFail($account->getQuoteSymbol());
        $quote_amount = min($this->amount, max(0, $quote_balance->getAmount() - $account->getSafetyAmount()));
        $price = $candles->getLastPrice();
        $base_amount =  $quote_amount / $price;
        return Order::createMarketBuy(
            $account,
            $candles->getPair(),
            $base_amount,
        );
    }
}