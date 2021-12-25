<?php

namespace App\Domain\Repository\Account;

use App\Domain\Model\Account\Account;

interface BalanceRepository
{
    public function findOfAccount(Account $account): array;
}