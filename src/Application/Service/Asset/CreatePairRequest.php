<?php

namespace App\Application\Service\Asset;


class CreatePairRequest
{
    private string $symbol;
    private string $base;
    private string $quote;
    private int $decimals;
    private int $vol_decimals;
    private string $order_min;
    private array $buy_leverages;
    private array $sell_leverages;

    public function __construct(
        string $symbol,
        string $base,
        string $quote,
        int $decimals,
        int $vol_decimals,
        string $order_min,
        array $buy_leverages,
        array $sell_leverages
    ) {
        $this->symbol = $symbol;
        $this->base = $base;
        $this->quote = $quote;
        $this->decimals = $decimals;
        $this->vol_decimals = $vol_decimals;
        $this->order_min = $order_min;
        $this->buy_leverages = $buy_leverages;
        $this->sell_leverages = $sell_leverages;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function getQuote(): string
    {
        return $this->quote;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getVolDecimals(): int
    {
        return $this->vol_decimals;
    }

    public function getOrderMin(): string
    {
        return $this->order_min;
    }

    public function getBuyLeverages(): array
    {
        return $this->buy_leverages;
    }

    public function getSellLeverages(): array
    {
        return $this->sell_leverages;
    }
}