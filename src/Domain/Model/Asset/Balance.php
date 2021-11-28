<?php

namespace App\Domain\Model\Asset;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Shared\Amount\Amount;
use App\Domain\Model\Shared\DateTracker\DateTracker;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

// Nota: un solo registro por <account_id, asset_id>, que se actualice y lance eventos
class Balance
{
    protected int $id;
    protected string $uuid;    // TODO puede que sobre en esta entidad.
    protected ?Account $account;
    protected Asset $asset;
    protected Amount $amount;
    protected DateTracker $date_tracker;

    public static function create(Asset $asset, Amount $amount): self
    {
        return new self($asset, $amount);
    }

    private function __construct(Asset $asset, Amount $amount) {
        $this->uuid = Uuid::uuid6();
        $this->asset = $asset;
        $this->amount = $amount;
        $this->date_tracker = DateTracker::create();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }

    public function getAsset(): Asset
    {
        return $this->asset;
    }

    public function getAssetSymbol(): string
    {
        return $this->asset->getSymbol();
    }

    public function update(Amount $amount): void
    {
        $this->amount = $amount;
        $this->date_tracker->update();
    }

    public function delete(): void
    {
        $this->amount = Amount::zero();
        $this->date_tracker->delete();
    }

    public function assignTo(Account $account): void
    {
        $this->account = $account;
    }


}