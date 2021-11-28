<?php

namespace App\Domain\Model\Asset;


use App\Domain\Exception\AlreadyAssignedException;

class Leverage
{

    // TODO change to Enum when PHP8.1 is available!
    public const OPERATION_BUY = 'buy';
    public const OPERATION_SELL = 'sell';

    protected ?Pair $pair;
    protected string $operation;  // TODO change to Enum when PHP8.1 is available!
    protected int $value;

    public static function createBuy(int $value): self
    {
        return new self(self::OPERATION_BUY, $value);
    }

    public static function createSell(int $value): self
    {
        return new self(self::OPERATION_SELL, $value);
    }

    private function __construct(string $operation, int $value)
    {
        $this->pair = null;
        $this->operation = $operation;
        $this->value = $value;
    }

    public function assignTo(Pair $pair): void
    {
        if (isset($this->pair)) {
            throw new AlreadyAssignedException("Can't assign Leverage to Pair {$pair->getSymbol()}. It is already assigned do");
        }
        $this->pair = $pair;
    }

    public function isBuy(): bool
    {
        return $this->operation === self::OPERATION_BUY;
    }

    public function isSell(): bool
    {
        return $this->operation === self::OPERATION_SELL;
    }
}