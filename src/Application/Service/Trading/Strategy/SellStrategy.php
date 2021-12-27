<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;

abstract class SellStrategy extends Strategy
{
    public function __construct(string $name) {
        parent::__construct($name, Strategy::OPERATION_SELL);
    }

    abstract public function getNumberOfCandles(): int;

    abstract public function checkCanSell(Account $account): bool;

    abstract public function run(Account $account, CandleCollection $candles): ?Order;
}