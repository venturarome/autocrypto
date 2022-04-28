<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

class SellNoLossesAllStrategy extends SellStrategy
{
    public const NAME = 'sell.no_losses.all';

    protected int $num_candles = 10;
    protected float $return_threshold = -1;


    public function __construct(array $custom_params = []) {
        $this->num_candles = $custom_params[Preference::NAME_SELL_NUM_CANDLES] ?? $this->num_candles;
        $this->return_threshold = $custom_params[Preference::NAME_SELL_RETURN_THRESHOLD] ?? $this->return_threshold;

        parent::__construct(self::NAME);
    }

    public function dumpConstants(): string
    {
        return "num_candles: " . $this->num_candles . PHP_EOL
            . "return_threshold: " . $this->return_threshold . PHP_EOL;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        if (!$account->canSell()) {
            return null;
        }

        $candles = $candles->filterLastCandles($this->num_candles);

        $base = $candles->getBase();
        if (!$account->hasBalanceOf($base)) {
            return null;
        }
        /** @var SpotBalance $base_balance */
        $base_balance = $account->getBalanceOf($base);

        if ($candles->getPercentageReturn() > $this->return_threshold               // Performance still good
            &&                                                                      // and
            $candles->getLastPrice() > 0.98 * $base_balance->getAveragePrice()      // Price over 98% of avg. purchase price
        ) {
            return null;
        }

        return Order::createMarketSell($account, $candles->getPair(), $base_balance->getAmount());
    }
}