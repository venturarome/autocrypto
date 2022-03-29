<?php

namespace App\Domain\Repository\Asset;

use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Asset\PairCollection;
use App\Domain\Model\Asset\SpotAsset;

interface PairRepository
{
    public function findBySymbol(string $symbol): ?Pair;
    public function findBySymbolOrFail(string $symbol): Pair;

    public function findByAssets(SpotAsset $base, SpotAsset $quote): ?Pair;
    public function findByAssetsOrFail(SpotAsset $base, SpotAsset $quote): Pair;

    public function findByAsset(SpotAsset $asset): PairCollection;

    public function findByQuote(SpotAsset $quote): PairCollection;
}