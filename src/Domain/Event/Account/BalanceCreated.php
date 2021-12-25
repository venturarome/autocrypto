<?php

namespace App\Domain\Event\Account;

use App\Domain\Event\ThrowableEvent;
use App\Domain\Model\Account\Balance;

class BalanceCreated extends ThrowableEvent
{
    protected const NAME = 'account.balance.created';

    public static function raise(Balance $balance): self
    {
        return new self($balance);
    }

    private function __construct(Balance $balance)
    {
        $content = [
            'account_reference' => $balance->getAccount()->getReference(),
            'amount' => $balance->getAmount(),
        ];

        parent::__construct($balance->getUuid(), $content);
    }
}