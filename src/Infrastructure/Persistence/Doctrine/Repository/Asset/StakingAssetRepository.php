<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Domain\Model\Asset\StakingAsset;
use App\Domain\Repository\Asset\StakingAssetRepository as StakingAssetRepositoryI;
use Doctrine\Persistence\ManagerRegistry;


class StakingAssetRepository extends AssetRepository implements StakingAssetRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StakingAsset::class);
    }

}