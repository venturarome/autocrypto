<?php

namespace App\Domain\Repository\Asset;

use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\Pair;

interface PairRepository
{
    public function findBySymbol(string $symbol): ?Pair;
    public function findBySymbolOrFail(string $symbol): Pair;

    public function findByAssets(Asset $base, Asset $quote): ?Pair;
    public function findByAssetsOrFail(Asset $base, Asset $quote): Pair;
}