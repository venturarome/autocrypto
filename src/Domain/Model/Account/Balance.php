<?php

namespace App\Domain\Model\Account;

use App\Domain\Model\Asset\Asset;
use Ramsey\Uuid\Uuid;


abstract class Balance
{
    public const TYPE_SPOT = 'spot';
    public const TYPE_STAKING = 'staking';

    protected int $id;
    protected string $uuid;
    protected ?Account $account;
    protected string $type; // Se puede quitar, nunca se está rellenando en el orm mapper.
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

    abstract public function getType(): string;

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

    abstract public function getAsset(): Asset;

    public function getAssetSymbol(): string
    {
        return $this->getAsset()->getSymbol();
    }

    public function getAssetName(): ?string
    {
        return $this->getAsset()->getName();
    }

    public function getAssetType(): ?string
    {
        return $this->getAsset()->getType();
    }

    public function getMinChange(): float
    {
        return 1 / (10**$this->getAsset()->getDecimals());
    }
}