<?php

namespace App\Domain\Mock\Trading;

use App\Domain\Mock\Asset\PairMocker;
use App\Domain\Mock\ModelMocker;
use App\Domain\Model\Trading\Candle;

class CandleMocker extends ModelMocker
{
    public static function create(array $data = []): Candle
    {
        $candle = Candle::create(
            $data['pair'] ?? PairMocker::create(),
            $data['timespan'] ?? 1,
            $data['timestamp'] ?? 1633046400,
            $data['open'] ?? 40.0,
            $data['high'] ?? 46.0,
            $data['low'] ?? 38.1,
            $data['close'] ?? 43.5,
            $data['volume'] ?? 12000.53,
            $data['trades'] ?? 128
        );
        unset($data['pair'], $data['timespan'], $data['timestamp'], $data['open'], $data['high'], $data['low'], $data['close'], $data['volume'], $data['trades']);

        self::bulk($candle, $data);

        return $candle;
    }
}