<?php

namespace App\Domain\Model\Account;

use App\Application\Exception\NotFoundException;
use App\Domain\Event\Asset\BalanceCreated;
use App\Domain\Event\Asset\BalanceDeleted;
use App\Domain\Event\Asset\BalanceUpdated;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\Balance;
use App\Domain\Model\Asset\BalanceCollection;
use App\Domain\Model\Shared\Amount\Amount;
use App\Domain\Model\Shared\DateTracker\DateTracker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ramsey\Uuid\UuidInterface;

class Account
{
    // TODO change to Enum when PHP8.1 is available!
    // TODO add and remove as necessary
    public const STATUS_PENDING_KEYS = 'pending-keys';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';

    protected int $id;
    protected string $uuid;
    protected string $reference;
    protected string $status; // TODO change to Enum when PHP8.1 is available!
    protected string $api_key;
    protected string $secret_key;
    protected DateTracker $date_tracker;

    protected Collection $balances;


    private function __construct() {
        // Want to instantiate? Use its factory!
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getKeys(): array
    {
        return [$this->api_key, $this->secret_key];
    }

    public function updateBalances(BalanceCollection $new_balances): array
    {
        $throwable_events = []; // I need to do this until I manage to raise events from Entities

        foreach ($this->getBalances() as $balance) {
            /** @var Balance $balance */
            $new_balance = $new_balances->findOneWithAssetSymbol($balance->getAssetSymbol());
            if (!$new_balance) {
                $balance->delete();
                $throwable_events[] = BalanceDeleted::raise($balance);
            }
            else if (Amount::equals($balance->getAmount(), $new_balance->getAmount())) {
                $balance->update($new_balance->getAmount());
                $throwable_events[] = BalanceUpdated::raise($balance);
            }
        }

        foreach ($new_balances as $new_balance) {
            /** @var Balance $new_balance */
            $balance = $this->getBalances()->findOneWithAssetSymbol($new_balance->getAssetSymbol());
            if (!$balance) {
                $new_balance->assignTo($this);
                $this->getBalances()->add($new_balance);
                $throwable_events[] = BalanceCreated::raise($new_balance);
            }
        }

        return $throwable_events;
    }

    private function getBalances(): BalanceCollection
    {
        if (!$this->balances instanceof BalanceCollection) {
            $this->balances = new BalanceCollection($this->balances->toArray());
        }
        return $this->balances;
    }

    public function hasBalanceOf(Asset $asset): bool
    {
        return (bool)$this->getBalances()->findOf($asset);
    }

}