<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Repository\Asset\SpotAssetRepository as SpotAssetRepositoryI;
use Doctrine\Persistence\ManagerRegistry;


class SpotAssetRepository extends AssetRepository implements SpotAssetRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotAsset::class);
    }

    // Just added to typehint the return. Is it any other way to do this and avoid overriding the method?
    public function findBySymbol(string $symbol): ?SpotAsset
    {
        return parent::findBySymbol($symbol);
    }

}