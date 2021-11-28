<?php

namespace App\Domain\Model\Asset;

use Doctrine\Common\Collections\ArrayCollection;

class BalanceCollection extends ArrayCollection
{
    // TODO ver si hay que tirarlo
    public function findOf(Asset $asset): ?Balance
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

    public function filterMissingInArrayOfSymbols(array $symbols): BalanceCollection
    {
        $this->filter(static function (Balance $balance) use ($symbols) {
            return !in_array($balance->getAssetSymbol(), $symbols, true);
        });
    }
}