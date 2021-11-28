<?php

namespace App\Domain\Repository\Asset;

use App\Domain\Model\Asset\Asset;

interface LeverageRepository
{
    public function findByAsset(Asset $asset): array;

    public function findBuyByAsset(Asset $asset): array;
}