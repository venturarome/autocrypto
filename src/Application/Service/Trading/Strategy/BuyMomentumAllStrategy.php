<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class BuyMomentumAllStrategy extends BuyStrategy
{
    public const NAME = 'buy.momentum.all';
    protected const SAFETY_MARGIN = 10; // Quote
    protected const MOMENTUM_RATIO = 3;

    private ?SpotBalance $balance_eur;


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

    public function checkCanBuy(Account $account): bool
    {
        return ($this->balance_eur = $account->getSpotBalances()->findOneWithAssetSymbol('EUR'))
            && $this->balance_eur->getAmount() - self::SAFETY_MARGIN > $this->balance_eur->getMinChange();
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        if($candles->count() === 0) {
            return null;
        }

        $average_momentum = $candles->getAverageClose() - $candles->getAverageOpen();
        $candles = $this->curateData($candles);
        $current_momentum = $candles->getAverageClose() - $candles->getAverageOpen();

        $quote = $candles->getQuote();
        if ($current_momentum < 0                                           // price going down
            ||                                                              // or
            $current_momentum < self::MOMENTUM_RATIO * $average_momentum    // low momentum ratio
            ||                                                              // or
            $account->hasBalanceOf($quote)                                  // already has a position
        ) {
            return null;
        }

        $price = $candles->getLastPrice();
        $quote_amount = min(self::BUY_AMOUNT_QUOTE, max(0, $this->balance_eur->getAmount() - self::SAFETY_MARGIN));
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
            //->filterLastCandles(5);
    }

}