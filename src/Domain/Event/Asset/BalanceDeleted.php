<?php

namespace App\Domain\Event\Asset;

use App\Domain\Event\ThrowableEvent;
use App\Domain\Model\Asset\Balance;

class BalanceDeleted extends ThrowableEvent
{
    protected const NAME = 'asset.balance.deleted';

    public static function raise(Balance $balance): self
    {
        return new self($balance);
    }

    private function __construct(Balance $balance)
    {
        $content = [
            'account_reference' => $balance->getAccount()->getReference(),
            'amount' => $balance->getAmount()->toString()
        ];

        parent::__construct($balance->getUuid(), $content);
    }
}