<?php

namespace App\Domain\Model\Trading;


use Ramsey\Uuid\Uuid;

abstract class Transaction
{
    public const TYPE_SPOT = 'spot';
    public const TYPE_STAKING = 'staking';

    // TODO change to Enum when PHP8.1 is available!
    public const OPERATION_TRADE = 'trade';
    public const OPERATION_DEPOSIT = 'deposit';
    public const OPERATION_WITHDRAW = 'withdraw';
    public const OPERATION_TRANSFER = 'transfer';
    public const OPERATION_MARGIN = 'margin';
    public const OPERATION_ROLLOVER = 'rollover';
    public const OPERATION_SPEND = 'spend';
    public const OPERATION_RECEIVE = 'receive';
    public const OPERATION_SETTLED = 'settled';
    public const OPERATION_ADJUSTMENT = 'adjustment';
    public const OPERATION_STAKING = 'staking';

    protected int $id;
    protected string $uuid;
    protected string $reference;
    protected string $type;
    protected string $operation;
    protected string $operation_reference;
    protected float $timestamp;
    protected float $amount;
    protected float $fee;


    protected function __construct(
        string $reference,
        string $type,
        string $operation,
        string $operation_reference,
        float $timestamp,
        float $amount,
        float $fee
    ) {
        $this->uuid = $this->uuid = Uuid::uuid6()->toString();
        $this->reference = $reference;
        $this->type = $type;
        $this->operation = $operation;
        $this->operation_reference = $operation_reference;
        $this->timestamp = $timestamp;
        $this->amount = $amount;
        $this->fee = $fee;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getOperationReference(): string
    {
        return $this->operation_reference;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    abstract public function getBalance();

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFee(): float
    {
        return $this->fee;
    }
}