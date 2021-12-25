<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Account\Balance;
use App\Domain\Model\Account\SpotBalance;


class SpotTransaction extends Transaction
{
    protected SpotBalance $spot_balance;

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

    public function getBalance(): Balance
    {
        return $this->getSpotBalance();
    }

    public function getSpotBalance(): SpotBalance
    {
        return $this->spot_balance;
    }
}