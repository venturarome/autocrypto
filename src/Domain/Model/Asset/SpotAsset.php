<?php

namespace App\Domain\Model\Asset;


class SpotAsset extends Asset
{

    // TODO change to Enum when PHP8.1 is available!
    public const TYPE_FIAT = 'fiat';
    public const TYPE_CRYPTO = 'crypto';

    protected ?string $name;           // kraken: no info on this (ej: Bitcoin)
    protected string $type;           // kraken: no info (only some hints, as 'most' fiat have Z___).    // TODO change to Enum when PHP8.1 is available!
    protected ?StakingAsset $staking_asset;


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
        parent::__construct($symbol, $decimals, $display_decimals);

        $this->name = $name;
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function canBeStaked(): bool
    {
        return !is_null($this->staking_asset);
    }
}