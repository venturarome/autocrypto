<?php

namespace App\Domain\Repository\Account;

use App\Domain\Model\Account\Account;

interface SpotBalanceRepository extends BalanceRepository
{
    public function findFiatOfAccount(Account $account): array;
}