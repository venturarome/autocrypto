<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleMap;
use App\Domain\Model\Trading\OrderCollection;

abstract class SellStrategy extends Strategy
{
    public function __construct(string $name) {
        parent::__construct($name, Strategy::OPERATION_SELL);
    }

    abstract public function getNumberOfCandles(): int;

    abstract public function getCandlesTimespan(): int;

    abstract public function checkCanSell(Account $account): bool;

    abstract public function run(Account $account, CandleMap $candle_map): OrderCollection;
}