<?php

namespace App\Domain\Model\Trading;

use Doctrine\Common\Collections\ArrayCollection;


class TransactionCollection extends ArrayCollection
{
    public function filterSpot(): SpotTransactionCollection
    {
        $spot = new SpotTransactionCollection();
        foreach ($this as $transaction) {
            /** @var Transaction $transaction */
            if ($transaction instanceof SpotTransaction) {
                $spot->add($transaction);
            }
        }
        return $spot;
    }

    public function filterStaking(): StakingTransactionCollection
    {
        $spot = new StakingTransactionCollection();
        foreach ($this as $transaction) {
            /** @var Transaction $transaction */
            if ($transaction instanceof StakingTransaction) {
                $spot->add($transaction);
            }
        }
        return $spot;
    }

    public function filterOfAssetSymbol(string $asset_symbol): static
    {
        $filtered = new static();
        foreach ($this as $transaction) {
            /** @var Transaction $transaction */
            if ($transaction->getAssetSymbol() === $asset_symbol) {
                $filtered->add($transaction);
            }
        }
        return $filtered;
    }

    public function filterOutOfAssetSymbol(string $asset_symbol): static
    {
        $filtered = new static();
        foreach ($this as $transaction) {
            /** @var Transaction $transaction */
            if ($transaction->getAssetSymbol() !== $asset_symbol) {
                $filtered->add($transaction);
            }
        }
        return $filtered;
    }

}