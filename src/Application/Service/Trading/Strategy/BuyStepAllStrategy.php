<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleMap;
use App\Domain\Model\Trading\Order;
use App\Domain\Model\Trading\OrderCollection;

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

//    public function getCandlesTimespan(): int
//    {
//        return 5;
//    }

    public function checkCanBuy(Account $account): bool
    {
        return ($this->balance_eur = $account->getSpotBalances()->findOneWithAssetSymbol('EUR'))
            && $this->balance_eur->getAmount() - self::SAFETY_MARGIN > $this->balance_eur->getMinChange();
    }

    public function run(Account $account, CandleMap $candle_map): OrderCollection
    {
        $orders = new OrderCollection();

        if($candle_map->count() === 0) {
            return $orders;
        }

//        $candle_map = $this->curateData($candle_map);

        $highest_performance_candle_collection = $candle_map->selectHighestPerformant();

        $performance = $highest_performance_candle_collection->getPerformance();
        if ($performance->getPercentageReturn() > self::MINIMUM_RETURN) {
            /** @var Candle $last_candle */
            $price = $highest_performance_candle_collection->getLastPrice();
            $base_amount = ($this->balance_eur->getAmount() - self::SAFETY_MARGIN) / $price;
            $orders->add(Order::createMarketBuy(
                $account,
                $performance->getPair(),
                $base_amount,
            ));
        }
        return $orders;
    }

    public function curateData(CandleMap $candle_map): CandleMap
    {
        return $candle_map
            //->fillGaps()
            // ->increaseTimespan($this->getCandlesTimespan())
            ->filterLastCandles($this->getNumberOfCandles());
    }


    public function getCandlesTimespan(): int
    {
        // TODO: Implement getCandlesTimespan() method.
    }


}