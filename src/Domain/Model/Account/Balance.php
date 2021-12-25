<?php

namespace App\Domain\Model\Account;

use Ramsey\Uuid\Uuid;


abstract class Balance
{
    public const TYPE_SPOT = 'spot';
    public const TYPE_STAKING = 'staking';

    protected int $id;
    protected string $uuid;
    protected ?Account $account;
    protected string $type;
    protected float $amount;

    protected function __construct(string $type, float $amount) {
        $this->uuid = Uuid::uuid6();
        $this->type = $type;
        $this->amount = $amount;
    }


    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function update(float $amount): void
    {
        $this->amount = $amount;
    }

    public function add(float $amount): void
    {
        $this->update($this->amount + $amount);
    }

    public function subtract(float $amount): void
    {
        if ($this->amount < $amount) {
            throw new \DomainException("Can't subtract $amount {$this->getAssetSymbol()} because there is only {$this->amount} available.");
        }
        $this->update($this->amount - $amount);
    }

    public function setZero(): void
    {
        $this->update(0);
    }

    public function isZero(): bool
    {
        return $this->amount < 1e-15;
    }

    public function assignTo(Account $account): void
    {
        $this->account = $account;
    }

    abstract public function getAsset();

    public function getAssetSymbol(): string
    {
        return $this->getAsset()->getSymbol();
    }

    public function getMinChange(): float
    {
        return 1 / (10**$this->getAsset()->getDecimals());
    }
}