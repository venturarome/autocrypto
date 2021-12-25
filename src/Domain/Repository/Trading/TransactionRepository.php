<?php

namespace App\Domain\Repository\Trading;

use App\Domain\Model\Trading\Transaction;


interface TransactionRepository
{
    public function findByReference(string $reference): ?Transaction;
}