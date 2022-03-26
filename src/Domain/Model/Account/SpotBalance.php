<?php

namespace App\Domain\Model\Account;

use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Model\Trading\FiscalResult;
use App\Domain\Model\Trading\SpotTransaction;
use App\Domain\Model\Trading\SpotTransactionCollection;
use Doctrine\Common\Collections\Collection;


class SpotBalance extends Balance
{
    protected SpotAsset $spot_asset;
    protected Collection $transactions;

    // Cache
    private FiscalResult $fiscal_result;

    public static function create(SpotAsset $spot_asset, float $amount = 0): self
    {
        return new self($spot_asset, $amount);
    }

    private function __construct(SpotAsset $spot_asset, float $amount) {
        parent::__construct(Balance::TYPE_SPOT, $amount);

        $this->spot_asset = $spot_asset;
        $this->transactions = new SpotTransactionCollection();
    }

    public function getType(): string
    {
        return self::TYPE_SPOT;
    }

    public function getAsset(): SpotAsset
    {
        return $this->getSpotAsset();
    }

    public function getSpotAsset(): SpotAsset
    {
        return $this->spot_asset;
    }

    private function getTransactions(): SpotTransactionCollection
    {
        if (!$this->transactions instanceof SpotTransactionCollection) {
            $this->transactions = new SpotTransactionCollection($this->transactions->toArray());
        }
        return $this->transactions;
    }

    private function getFiscalResult(): FiscalResult
    {
        if (!isset($this->fiscal_result)) {
            $this->fiscal_result = $this->getTransactions()->getFiscalResult();
        }
        return $this->fiscal_result;
    }

    public function getAveragePrice(): float
    {
        return $this->getFiscalResult()->getAveragePrice();
    }

    public function getLastTransactionTimestamp(): ?int
    {
        /* @var bool|SpotTransaction $last_transaction */
        $last_transaction = $this->transactions->last();
        return $last_transaction !== false ? $last_transaction->getTimestamp() : null;
    }

    public function addTransaction(SpotTransaction $transaction): void
    {
        $this->getTransactions()->add($transaction);
    }
}