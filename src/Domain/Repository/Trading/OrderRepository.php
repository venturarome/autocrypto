<?php

namespace App\Domain\Repository\Trading;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\Order;
use App\Domain\Model\Trading\OrderCollection;

interface OrderRepository
{
    public function findByTxid(string $txid): ?Order;
    public function findByTxidOrFail(string $txid): Order;
    public function findOfAccount(Account $account): OrderCollection;
    public function findUncheckedOfAccount(Account $account): OrderCollection;
    public function findOpenOfAccount(Account $account): OrderCollection;
}