<?php

namespace App\Domain\Model\Account;

use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\StakingAsset;
use App\Domain\Model\Trading\StakingTransactionCollection;
use Doctrine\Common\Collections\Collection;


class StakingBalance extends Balance
{
    protected StakingAsset $staking_asset;
    protected Collection $transactions;

    public static function create(StakingAsset $staking_asset, float $amount = 0): self
    {
        return new self($staking_asset, $amount);
    }

    private function __construct(StakingAsset $staking_asset, float $amount) {
        parent::__construct(Balance::TYPE_STAKING, $amount);

        $this->staking_asset = $staking_asset;
        $this->transactions = new StakingTransactionCollection();
    }

    public function getAsset(): Asset
    {
        return $this->getStakingAsset();
    }

    public function getStakingAsset(): StakingAsset
    {
        return $this->staking_asset;
    }

}