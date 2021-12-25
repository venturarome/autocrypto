<?php

namespace App\Application\Service\Asset;


class CreateCandleRequest
{
    private string $pair_symbol;
    private int $timespan;
    private int $timestamp;
    private string $open;
    private string $high;
    private string $low;
    private string $close;
    private string $volume;
    private int $trades;

    public function __construct(
        string $pair_symbol,
        int $timespan,
        int $timestamp,
        string $open,
        string $high,
        string $low,
        string $close,
        string $volume,
        int $trades,
    ) {
        $this->pair_symbol = $pair_symbol;
        $this->timespan = $timespan;
        $this->timestamp = $timestamp;
        $this->open = $open;
        $this->high = $high;
        $this->low = $low;
        $this->close = $close;
        $this->volume = $volume;
        $this->trades = $trades;
    }

    public function getPairSymbol(): string
    {
        return $this->pair_symbol;
    }

    public function getTimespan(): int
    {
        return $this->timespan;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getOpen(): string
    {
        return $this->open;
    }

    public function getHigh(): string
    {
        return $this->high;
    }

    public function getLow(): string
    {
        return $this->low;
    }

    public function getClose(): string
    {
        return $this->close;
    }

    public function getVolume(): string
    {
        return $this->volume;
    }

    public function getTrades(): int
    {
        return $this->trades;
    }

}