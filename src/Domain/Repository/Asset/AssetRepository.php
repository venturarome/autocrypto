<?php

namespace App\Domain\Repository\Asset;

use App\Domain\Model\Asset\Asset;

interface AssetRepository
{
    public function findBySymbol(string $symbol): ?Asset;
    public function findBySymbolOrFail(string $symbol): Asset;
}