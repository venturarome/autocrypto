<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleMap;

abstract class Strategy
{
    public const OPERATION_BUY = 'buy';
    public const OPERATION_SELL = 'sell';

    protected string $name;
    protected string $operation;


    public function __construct(string $name, string $operation)
    {
        $this->name = $name;
        $this->operation = $operation;
    }

    abstract public function getNumberOfCandles(): int;

    // TODO a lo mejor no hace falta.
    abstract public function getCandlesTimespan(): int;

    abstract public function run(Account $account, CandleMap $candle_map);
}