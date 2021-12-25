<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;

class Performance {

//    public const PERIOD_DAY = 'day';                // 1 day
//    public const PERIOD_WEEK = 'week';              // 7 days
//    public const PERIOD_MONTH = 'month';            // calendar month (starts on 1st each month)
//    public const PERIOD_QUARTER = 'year';           // calendar year (starts on 1st January)

    protected Pair $pair;
    protected float $return;    // in percentage! 1.05 means +5%
//    protected string $period;
    protected int $from;
    protected int $to;

    // TODO debería tener en cuenta sobre qué divisa se toma la referencia?
    //      Se podría crear un PerformanceMap, que sea una colección de PerformanceCollections.
    //      Así tendríamos las rentabilidades diarias de cada una de las divisas de una cartera.

    public function __construct(Pair $pair, float $return, int $from, int $to) {
        $this->pair = $pair;
        $this->return = $return;
        $this->from = $from;
        $this->to = $to;
    }

    public static function fromCandle(Candle $candle): self
    {
        $open = $candle->getOpen();
        $close = $candle->getClose();

        return new self(
            $candle->getPair(),
            1 + ($close-$open)/$open,
            $candle->getTimestamp(),
            $candle->getNextTimestamp()
        );
    }

    public static function fromCandleCollection(CandleCollection $candle_collection): self
    {
        $open = $candle_collection->first()->getOpen();
        $close = $candle_collection->last()->getClose();

        return new self(
            $candle_collection->getPair(),
            1 + ($close-$open)/$open,
            $candle_collection->first()->getTimestamp(),
            $candle_collection->last()->getNextTimestamp()
        );
    }

    public function getPair(): Pair
    {
        return $this->pair;
    }

    public function getReturn(): float
    {
        return $this->return;
    }

    public function getPercentageReturn(): float
    {
        return 100 * ($this->return - 1);
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }

//    public static function calculate(float ...$prices): float
//    {
//        $return = 1;
//        $last = $prices[0];
//        foreach ($prices as $price) {
//            $return *= ($price-$last)/$last + 1;
//            $last = $price;
//            echo $return . PHP_EOL;
//        }
//        return $return - 1;
//    }

    public function hasHigherReturnThan(Performance $other): bool
    {
        $timespan_this = $this->to - $this->from;
        $timespan_other = $other->to - $other->from;
        if ($timespan_this !== $timespan_other) {
            throw new \InvalidArgumentException("Returns from different timespans are not comparable!");
        }

        return $this->return > $other->return;
    }

}