<?php

namespace App\Domain\Repository\Trading;


use App\Domain\Model\Account\StakingBalance;

interface StakingTransactionRepository extends TransactionRepository
{
    public function findOfStakingBalance(StakingBalance $balance);
}