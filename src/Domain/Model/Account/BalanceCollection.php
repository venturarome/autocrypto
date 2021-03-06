<?php

namespace App\Domain\Model\Account;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\AssetCollection;
use Doctrine\Common\Collections\ArrayCollection;

class BalanceCollection extends ArrayCollection
{
    public function filterSpot(): SpotBalanceCollection
    {
        $spot = new SpotBalanceCollection();
        foreach ($this as $balance) {
            /** @var Balance $balance */
            if ($balance instanceof SpotBalance) {
                $spot->add($balance);
            }
        }
        return $spot;
    }

    public function filterStaking(): StakingBalanceCollection
    {
        $staking = new StakingBalanceCollection();
        foreach ($this as $balance) {
            /** @var Balance $balance */
            if ($balance instanceof SpotBalance) {
                $staking->add($balance);
            }
        }
        return $staking;
    }

    public function findOfAsset(Asset $asset): ?Balance
    {
        $symbol = $asset->getSymbol();
        foreach($this as $balance) {
            /** @var Balance $balance */
            if ($balance->getAssetSymbol() === $symbol) {
                return $balance;
            }
        }
        return null;
    }
    // TODO el de arriba y el de abajo son iguales
    public function findOneWithAssetSymbol(string $symbol): ?Balance
    {
        foreach ($this as $balance) {
            /** @var Balance $balance */
            if ($balance->getAssetSymbol() === $symbol) {
                return $balance;
            }
        }
        return null;
    }

    public function findOneWithAssetSymbolOrFail(string $symbol): Balance
    {
        $balance = $this->findOneWithAssetSymbol($symbol);
        if (!$balance) {
            throw new NotFoundException("Balance with asset symbol $symbol not found!");
        }
        return $balance;
    }

    public function findOfAssetOrFail(Asset $asset): Balance
    {
        $balance = $this->findOfAsset($asset);
        if (!$balance) {
            throw new NotFoundException("Balance with asset {$asset->getSymbol()} not found!");
        }
        return $balance;
    }

    public function getAssets(): AssetCollection
    {
        $assets = new AssetCollection();
        foreach ($this as $balance) {
            /** @var Balance $balance */
            $assets->add($balance->getAsset());
        }
        return $assets;
    }

    public function filterMissingInArrayOfSymbols(array $symbols): self
    {
        return $this->filter(static function (Balance $balance) use ($symbols) {
            return !in_array($balance->getAssetSymbol(), $symbols, true);
        });
    }

    public function filterNonZero(): self
    {
        return $this->filter(static function (Balance $balance) {
            return $balance->getAmount() >= $balance->getMinChange();
        });
    }
}