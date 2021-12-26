<?php

namespace App\Domain\Repository\Trading;


use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\SpotTransactionCollection;

interface SpotTransactionRepository extends TransactionRepository
{
    public function findOfBalance(SpotBalance $balance);

    public function findByOperationReference(string $operation_reference): SpotTransactionCollection;
}