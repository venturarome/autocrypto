<?php

namespace App\Domain\Model\Trading;


use App\Domain\Model\Account\Balance;
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
        $this->throwIfUnclassifiedOperation();
        $this->operation_reference = $operation_reference;
        $this->timestamp = $timestamp;
        $this->amount = $amount;
        $this->fee = $fee;
    }

    protected function throwIfUnclassifiedOperation(): void
    {
        if (in_array($this->operation, [self::OPERATION_MARGIN, self::OPERATION_ROLLOVER, self::OPERATION_SETTLED, self::OPERATION_ADJUSTMENT], true)) {
            throw new \DomainException("Unclassified Transaction::operation: {$this->operation}. Please, classify it on 'trade' or 'transfer'.");
        }
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

    /** There is a base and a quote asset */
    public function isTrade(): bool
    {
        return in_array($this->operation, self::getTradeOperations(), true);
    }

    protected static function getTradeOperations(): array
    {
        return [
            self::OPERATION_SPEND,      // Sell, from the App
            self::OPERATION_RECEIVE,    // Buy, from the App
            self::OPERATION_TRADE,      // Buy or Sell, from REST
        ];
    }

    /** Only moves money */
    public function isTransfer(): bool
    {
        return in_array($this->operation, self::getTransferOperations(), true);
    }

    protected static function getTransferOperations(): array
    {
        return [
            self::OPERATION_DEPOSIT,    // Add money to the platform
            self::OPERATION_WITHDRAW,   // Add money to the platform
            self::OPERATION_TRANSFER,   // So far, for staking/unstaking
            self::OPERATION_STAKING,    // Receive rewards for staked assets
        ];
    }

    public function getOperationReference(): string
    {
        return $this->operation_reference;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFee(): float
    {
        return $this->fee;
    }

    abstract public function getBalance();

    public function getAssetSymbol(): string
    {
        return $this->getBalance()->getAssetSymbol();
    }
}