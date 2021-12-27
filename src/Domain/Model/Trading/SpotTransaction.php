<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Account\Balance;
use App\Domain\Model\Account\SpotBalance;


class SpotTransaction extends Transaction
{
    protected SpotBalance $spot_balance;
    protected ?float $price;

    public function __construct(
        string $reference,
        string $operation,
        string $operation_reference,
        float $timestamp,
        float $amount,
        float $fee,
        SpotBalance $spot_balance
    ) {
        parent::__construct($reference, Transaction::TYPE_SPOT, $operation, $operation_reference, $timestamp, $amount, $fee);

        $this->spot_balance = $spot_balance;
    }

    public function getBalance(): SpotBalance
    {
        return $this->getSpotBalance();
    }

    public function getSpotBalance(): SpotBalance
    {
        return $this->spot_balance;
    }

    public function isFromCryptoAsset(): bool
    {
        return $this->getBalance()->getAsset()->isCrypto();
    }

    public static function setPriceFromCounterparts(self $t1, self $t2): void
    {
        // Crypto ==> Base ==> contains 'price'
        // Fiat ==> Quote
        $t1_asset = $t1->getBalance()->getAsset();
        $t2_asset = $t2->getBalance()->getAsset();

        if ($t1_asset->isCrypto() && $t2_asset->isFiat()) {
            $t1->setPrice(abs($t2->getAmount() / $t1->getAmount()));
        }
        else if ($t2_asset->isCrypto() && $t1_asset->isFiat()) {
            $t2->setPrice(abs($t1->getAmount() / $t2->getAmount()));
        }
        else {
            throw new \DomainException("Both Transaction counterparts are either Crypto or Fiat!!!");
        }
    }

    private function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }
}