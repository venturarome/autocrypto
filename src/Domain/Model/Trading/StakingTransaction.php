<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Account\Balance;
use App\Domain\Model\Account\StakingBalance;


class StakingTransaction extends Transaction
{
    protected StakingBalance $staking_balance;

    public function __construct(
        string $reference,
        string $operation,
        string $operation_reference,
        float $timestamp,
        float $amount,
        float $fee,
        StakingBalance $staking_balance
    ) {
        parent::__construct($reference, Transaction::TYPE_STAKING, $operation, $operation_reference, $timestamp, $amount, $fee);

        $this->staking_balance = $staking_balance;
    }

    public function getBalance(): StakingBalance
    {
        return $this->getStakingBalance();
    }

    public function getStakingBalance(): StakingBalance
    {
        return $this->staking_balance;
    }
}