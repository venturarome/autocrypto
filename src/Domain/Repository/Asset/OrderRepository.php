<?php

namespace App\Domain\Repository\Asset;

use App\Domain\Model\Account\Account;

interface OrderRepository
{
    public function findOfAccount(Account $account): array;

    public function findUncheckedOfAccount(Account $account): array;

    public function findOpenOfAccount(Account $account): array;
}