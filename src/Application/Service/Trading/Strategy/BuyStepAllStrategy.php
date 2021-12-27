<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

class BuyStepAllStrategy extends BuyStrategy
{
    public const NAME = 'buy.step.all';
    protected const SAFETY_MARGIN = 10; // Quote

    private ?SpotBalance $balance_eur;


    // TODO parametrizar
    private const MINIMUM_RETURN = 2;

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
        $candles = $this->curateData($candles);

        $performance = $candles->getPerformance();
        if ($performance->getPercentageReturn() <= self::MINIMUM_RETURN) {
            return null;
        }

        $price = $candles->getLastPrice();
        $base_amount = ($this->balance_eur->getAmount() - self::SAFETY_MARGIN) / $price;
        return Order::createMarketBuy(
            $account,
            $performance->getPair(),
            $base_amount,
        );
    }

    public function curateData(CandleCollection $candles): CandleCollection
    {
        return $candles
            ->filterLastCandles($this->getNumberOfCandles());
    }

}