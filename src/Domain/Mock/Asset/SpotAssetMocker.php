<?php

namespace App\Domain\Mock\Asset;

use App\Domain\Mock\ModelMocker;
use App\Domain\Model\Asset\SpotAsset;

class SpotAssetMocker extends ModelMocker
{
    public static function create(array $data = []): SpotAsset
    {
        $spotAsset = SpotAsset::create(
            $data['symbol'] ?? 'ETH',
            $data['name'] ?? 'Ethereum',
            $data['decimals'] ?? 8,
            $data['display_decimals'] ?? 4,
            $data['type'] ?? SpotAsset::TYPE_CRYPTO
        );
        unset($data['symbol'], $data['name'], $data['decimals'], $data['display_decimals'], $data['type']);

        self::bulk($spotAsset, $data);

        return $spotAsset;
    }
}