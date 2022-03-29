<?php

namespace App\Domain\Model\Asset;

use App\Domain\Model\Shared\DateTracker\DateTracker;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\Uuid;

class Pair
{
    protected int $id;
    protected string $uuid;
    protected string $symbol;     // kraken: name
    protected SpotAsset $base;
    protected SpotAsset $quote;
    protected int $decimals;      // kraken: pair_decimals: it is price precision
    protected int $vol_decimals;  // kraken: lot_decimals: it is the maximal precision of order size (volume), which is in base currency
    protected float $order_min;   // kraken: ordermin: Minimum order size (in terms of base currency)s
    protected Collection $leverages;    // Leverage[]

    public static function create(
        string $symbol,
        SpotAsset $base,
        SpotAsset $quote,
        int $decimals,
        int $vol_decimals,
        float $order_min,
        LeverageCollection $leverages
    ): self
    {
        return new self($symbol, $base, $quote, $decimals, $vol_decimals, $order_min, $leverages);
    }

    private function __construct(
        string $symbol,
        SpotAsset $base,
        SpotAsset $quote,
        int $decimals,
        int $vol_decimals,
        float $order_min,
        LeverageCollection $leverages
    ) {
        $this->uuid = Uuid::uuid6()->toString();
        $this->symbol = $symbol;
        $this->base = $base;
        $this->quote = $quote;
        $this->decimals = $decimals;
        $this->vol_decimals = $vol_decimals;
        $this->order_min = $order_min;
        $this->leverages = $leverages->assignTo($this);
        $this->date_tracker = DateTracker::create();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getBase(): SpotAsset
    {
        return $this->base;
    }

    public function getQuote(): SpotAsset
    {
        return $this->quote;
    }

    public function getBaseSymbol(): string
    {
        return $this->base->getSymbol();
    }

    public function getQuoteSymbol(): string
    {
        return $this->quote->getSymbol();
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getOrderMin(): float
    {
        return $this->order_min;
    }
}