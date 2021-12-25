<?php

namespace App\Domain\Repository\Trading;


use App\Domain\Model\Account\SpotBalance;

interface SpotTransactionRepository extends TransactionRepository
{
    public function findOfBalance(SpotBalance $balance);
}