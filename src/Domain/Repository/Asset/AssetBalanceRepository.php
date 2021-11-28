<?php

namespace App\Domain\Repository\Asset;

use App\Domain\Model\Account\Account;

interface AssetBalanceRepository
{
    public function findOfAccount(Account $account): array;

    public function findFiatOfAccount(Account $account): array;
}