<?php

namespace App\Domain\Model\Asset;

use Ramsey\Uuid\Uuid;


abstract class Asset
{

    protected int $id;
    protected string $uuid;
    protected string $symbol;         // kraken: name (ej: BTC)
    protected int $decimals;          // kraken: decimal (for record keeping)
    protected int $display_decimals;  // kraken: display_decimals (for output displaying)


    protected function  __construct(string $symbol, int $decimals, int $display_decimals) {
        $this->uuid = Uuid::uuid6()->toString();
        $this->symbol = $symbol;
        $this->decimals = $decimals;
        $this->display_decimals = $display_decimals;
    }


    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getDecimals(): float
    {
        return $this->decimals;
    }

    public function getDisplayDecimals(): float
    {
        return $this->decimals;
    }
}