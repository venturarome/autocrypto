<?php

namespace App\Domain\Mock\Asset;

use App\Domain\Mock\ModelMocker;

use App\Domain\Model\Asset\LeverageCollection;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Asset\SpotAsset;

class PairMocker extends ModelMocker
{
    public static function create(array $data = []): Pair
    {
        $pair = Pair::create(
            $data['symbol'] ?? 'SOLEUR',
            $data['base'] ?? SpotAssetMocker::create(['symbol' => 'SOL', 'name' => 'Solana']),
            $data['quote'] ?? SpotAssetMocker::create(['symbol' => 'EUR', 'name' => 'Euro', 'type' => SpotAsset::TYPE_FIAT]),
            $data['decimals'] ?? 8,
            $data['vol_decimals'] ?? 4,
            $data['order_min'] ?? 0.0001,
            $data['leverages'] ?? new LeverageCollection()
        );
        unset($data['symbol'], $data['base'], $data['quote'], $data['decimals'], $data['vol_decimals'], $data['order_min'], $data['leverages']);

        self::bulk($pair, $data);

        return $pair;
    }
}