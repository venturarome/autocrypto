<?php

namespace App\Domain\Model\Asset;

use App\Domain\Model\Shared\DateTracker\DateTracker;
use Ramsey\Uuid\Uuid;

class Asset
{

    // TODO change to Enum when PHP8.1 is available!
    public const TYPE_FIAT = 'fiat';
    public const TYPE_CRYPTO = 'crypto';

    private int $id;
    private string $uuid;
    private string $symbol;         // kraken: name (ej: BTC)
    private ?string $name;           // kraken: no info on this (ej: Bitcoin)
    private int $decimals;          // kraken: decimal (for record keeping)
    private int $display_decimals;  // kraken: display_decimals (for output displaying)
    private string $type;           // kraken: no info (only some hints, as 'most' fiat have Z___).    // TODO change to Enum when PHP8.1 is available!

    // TODO private StakingAsset $staking_asset;    // TODO crear Stake -- es el

    private DateTracker $date_tracker;

    public static function create(string $symbol, ?string $name, int $decimals, int $display_decimals, string $type): self
    {
        return new self($symbol, $name, $decimals, $display_decimals, $type);
    }

    private function  __construct(
        string $symbol,
        ?string $name,
        int $decimals,
        int $display_decimals,
        string $type
    ) {
        $this->uuid = Uuid::uuid6()->toString();
        $this->symbol = $symbol;
        $this->name = $name;
        $this->decimals = $decimals;
        $this->display_decimals = $display_decimals;
        $this->type = $type;
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
}