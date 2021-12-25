<?php

namespace App\Application\Service\Asset;


class CreateSpotAssetRequest
{
    private string $symbol;
    private ?string $name;
    private int $decimals;
    private int $display_decimals;
    private string $extended_symbol;

    public function __construct(string $symbol, ?string $name, int $decimals, int $display_decimals, string $extended_symbol)
    {
        $this->symbol = $symbol;
        $this->name = $name;
        $this->decimals = $decimals;
        $this->display_decimals = $display_decimals;
        $this->extended_symbol = $extended_symbol;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function getDisplayDecimals(): int
    {
        return $this->display_decimals;
    }

    public function getExtendedSymbol(): string
    {
        return $this->extended_symbol;
    }
}