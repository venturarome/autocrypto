<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Shared\Amount\Amount;

class Candle
{
    public const SECONDS_PER_MINUTE = 60;

    // TODO change to Enum when PHP8.1 is available!
    public const TIMESPAN_1 = 1;
    public const TIMESPAN_5 = 5;
    public const TIMESPAN_15 = 15;
    public const TIMESPAN_30 = 30;
    public const TIMESPAN_60 = 60;
    public const TIMESPAN_240 = 240;
    public const TIMESPAN_1440 = 1440;
    public const TIMESPAN_10080 = 10080;
    public const TIMESPAN_21600 = 21600;

    protected int $id;
    protected Pair $pair;
    protected int $timespan;    // TODO change to Enum when PHP8.1 is available!
    protected int $timestamp;
    protected float $open;
    protected float $high;
    protected float $low;
    protected float $close;
    protected float $volume;
    protected int $trades;


    public static function create(
        Pair $pair,
        int $timespan,
        int $timestamp,
        float $open,
        float $high,
        float $low,
        float $close,
        float $volume,
        int $trades
    ): self
    {
        return new self($pair, $timespan, $timestamp, $open, $high, $low, $close, $volume, $trades);
    }

    public static function interpolate(float $weight_c1, Candle $c1, Candle $c2): self
    {
        return new self(
            $c1->getPair(),
            $c1->getTimespan(),
            $c1->getNextTimestamp(),
            $c1->getOpen() + (1 - $weight_c1) * ($c2->getOpen() - $c1->getOpen()),
            $c1->getHigh() + (1 - $weight_c1) * ($c2->getHigh() - $c1->getHigh()),
            $c1->getLow() + (1 - $weight_c1) * ($c2->getLow() - $c1->getLow()),
            $c1->getClose() + (1 - $weight_c1) * ($c2->getClose() - $c1->getClose()),
            $c1->getVolume() + (1 - $weight_c1) * ($c2->getVolume() - $c1->getVolume()),
            (int)($c1->getTrades() + (1 - $weight_c1) * ($c2->getTrades() - $c1->getTrades()))
        );
    }

    private function __construct(
        Pair $pair,
        int $timespan,
        int $timestamp,
        float $open,
        float $high,
        float $low,
        float $close,
        float $volume,
        int $trades
    ) {
        $this->pair = $pair;
        $this->timespan = $timespan;
        $this->timestamp = $timestamp;
        $this->open = $open;
        $this->high = $high;
        $this->low = $low;
        $this->close = $close;
        $this->volume = $volume;
        $this->trades = $trades;
    }


    public function getPair(): Pair
    {
        return $this->pair;
    }

    public function getPairSymbol(): string
    {
        return $this->pair->getSymbol();
    }

    public function getTimespan(): int
    {
        return $this->timespan;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getOpen(): float
    {
        return $this->open;
    }

    public function getHigh(): float
    {
        return $this->high;
    }

    public function getLow(): float
    {
        return $this->low;
    }

    public function getClose(): float
    {
        return $this->close;
    }

    public function getVolume(): float
    {
        return $this->volume;
    }

    public function getTrades(): int
    {
        return $this->trades;
    }

    public function getNextTimestamp(): int
    {
        return $this->getTimestamp() + $this->getTimespan() * self::SECONDS_PER_MINUTE;
    }

}