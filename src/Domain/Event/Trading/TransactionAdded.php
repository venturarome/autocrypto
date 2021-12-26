<?php

namespace App\Domain\Event\Trading;

use App\Domain\Event\ThrowableEvent;
use App\Domain\Model\Trading\Transaction;

class TransactionAdded extends ThrowableEvent
{
    protected const NAME = 'trading.transaction.added';

    public static function raise(Transaction $transaction): self
    {
        return new self($transaction);
    }

    private function __construct(Transaction $transaction)
    {
        $content = [
            'account_reference' => $transaction->getBalance()->getAccount()->getReference(),
            'asset_symbol' => $transaction->getBalance()->getAssetSymbol(),
            'operation' => $transaction->getOperation(),
            'amount' => $transaction->getAmount(),
            'fee' => $transaction->getFee(),
        ];

        parent::__construct($transaction->getUuid(), $content);
    }
}