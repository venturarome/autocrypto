<?php

namespace App\Application\Service\Trading\Strategy;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Order;


class SellNullStrategy extends SellStrategy
{
    public const NAME = 'sell.null';

    public function __construct() {
        parent::__construct(self::NAME);
    }

    public static function dumpConstants(): string
    {
        return "";
    }

    public function getNumberOfCandles(): int
    {
        return 0;
    }

    public function run(Account $account, CandleCollection $candles): ?Order
    {
        return null;
    }
}