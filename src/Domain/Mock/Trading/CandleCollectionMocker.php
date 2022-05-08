<?php

namespace App\Domain\Mock\Trading;

use App\Domain\Mock\Asset\PairMocker;
use App\Domain\Model\Trading\CandleCollection;

use App\Domain\Mock\ModelMocker;

class CandleCollectionMocker extends ModelMocker
{
    public static function create(array $data = []): CandleCollection
    {
        $candleCollection = new CandleCollection(
            $data['pair'] ?? PairMocker::create(),
            $data['timespan'] ?? 1,
            $data['elements'] ?? [],
        );
        unset($data['pair'], $data['timespan'], $data['elements']);

        self::bulk($candleCollection, $data);

        return $candleCollection;
    }
}