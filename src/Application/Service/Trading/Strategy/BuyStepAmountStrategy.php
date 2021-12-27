<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;
use App\Domain\Model\Trading\OrderCollection;

class BuyStepAmountStrategy extends BuyStrategy
{
    public const NAME = 'buy.step.amount';
    protected const SAFETY_MARGIN = 10; // Quote

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

    public function run(Account $account, CandleCollection $candles): OrderCollection
    {
        $orders = new OrderCollection();

        if($candles->count() === 0) {
            return $orders;
        }

        $crypto_balances = $account->getSpotBalances()->filterCrypto();
        $owned_crypto_asset_symbols = $crypto_balances->getAssets()->getSymbolsArray();

        $candles = $this->curateData($candles);

        $quote = $candles->getPair()->getQuote();
        if ($candles->getPerformance()->getReturn() < self::MINIMUM_RETURN     // poor performance
            || in_array($quote->getSymbol(), $owned_crypto_asset_symbols, true)     // or already has a position
        ) {
            return $orders;
        }

        /** @var Candle $last_candle */
        $price = $candles->getLastPrice();
        $quote_amount = min(self::BUY_AMOUNT_QUOTE, max(0, $this->balance_eur->getAmount() - self::SAFETY_MARGIN));
        $base_amount =  $quote_amount / $price;
        $orders->add(Order::createMarketBuy(
            $account,
            $candles->getPair(),
            $base_amount,
        ));

        return $orders;
    }

    public function curateData(CandleCollection $candles): CandleCollection
    {
        return $candles
            //->fillGaps()
            ->filterLastCandles($this->getNumberOfCandles());
    }

}