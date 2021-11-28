<?php

namespace App\Domain\Event\Account;

use App\Domain\Event\ThrowableEvent;
use App\Domain\Model\Account\Account;

class AccountCreated extends ThrowableEvent
{
    protected const NAME = 'account.account.created';

    public static function raise(Account $account): self
    {
        return new self($account);
    }

    private function __construct(Account $account)
    {
        $content = [
            'reference' => $account->getReference()
        ];

        parent::__construct($account->getUuid(), $content);
    }



}