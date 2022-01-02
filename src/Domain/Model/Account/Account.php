<?php

namespace App\Domain\Model\Account;

use App\Domain\Event\Account\BalanceCreated;
use App\Domain\Event\Account\BalanceUpdated;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Model\Asset\StakingAsset;
use App\Domain\Model\Shared\DateTracker\DateTracker;
use App\Domain\Model\Trading\Order;
use Doctrine\Common\Collections\Collection;


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

    protected Collection $spot_balances;
    protected Collection $staking_balances;
    protected Collection $preferences;


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

    public function updateBalances(SpotBalanceCollection $new_balances): array
    {
        $new_spot_balances = $new_balances->filterSpot();
        $spot_events = $this->updateSpotBalances($new_spot_balances);

        $new_staking_balances = $new_balances->filterStaking();
        $staking_events = $this->updateStakingBalances($new_staking_balances);

        return array_merge($spot_events, $staking_events);
    }

    public function updateSpotBalances(SpotBalanceCollection $new_balances): array
    {
        $throwable_events = []; // I need to do this until I manage to raise events from Entities

        // Update current balances
        foreach ($this->getSpotBalances() as $balance) {
            /** @var SpotBalance $balance */

            $new_balance = $new_balances->findOneWithAssetSymbol($balance->getAssetSymbol());
            if (!$new_balance && !$balance->isZero()) {
                $balance->setZero();
                $throwable_events[] = BalanceUpdated::raise($balance);
            }
            else if (abs($balance->getAmount() - $new_balance->getAmount()) > $balance->getMinChange()) {
                $balance->update($new_balance->getAmount());
                $throwable_events[] = BalanceUpdated::raise($balance);
            }
        }

        // Add new balances
        foreach ($new_balances as $new_balance) {
            /** @var SpotBalance $new_balance */
            $balance = $this->getSpotBalances()->findOfAsset($new_balance->getAsset());
            if (!$balance) {
                $new_balance->assignTo($this);
                $this->getSpotBalances()->add($new_balance);
                $throwable_events[] = BalanceCreated::raise($new_balance);
            }
        }

        return $throwable_events;
    }
    // TODO try to merge in one method.
    public function updateStakingBalances(StakingBalanceCollection $new_balances): array
    {
        $throwable_events = []; // I need to do this until I manage to raise events from Entities

        // Update current balances
        foreach ($this->getStakingBalances() as $balance) {
            /** @var StakingBalance $balance */

            $new_balance = $new_balances->findOneWithAssetSymbol($balance->getAssetSymbol());
            if (!$new_balance && !$balance->isZero()) {
                $balance->setZero();
                $throwable_events[] = BalanceUpdated::raise($balance);
            }
            else if (abs($balance->getAmount() - $new_balance->getAmount()) > $balance->getMinChange()) {
                $balance->update($new_balance->getAmount());
                $throwable_events[] = BalanceUpdated::raise($balance);
            }
        }

        // Add new balances
        foreach ($new_balances as $new_balance) {
            /** @var StakingBalance $new_balance */
            $balance = $this->getStakingBalances()->findOfAsset($new_balance->getAsset());
            if (!$balance) {
                $new_balance->assignTo($this);
                $this->getStakingBalances()->add($new_balance);
                $throwable_events[] = BalanceCreated::raise($new_balance);
            }
        }

        return $throwable_events;
    }

    public function getSpotBalances(): SpotBalanceCollection
    {
        if (!$this->spot_balances instanceof SpotBalanceCollection) {
            $this->spot_balances = new SpotBalanceCollection($this->spot_balances->toArray());
        }
        return $this->spot_balances;
    }

    public function getStakingBalances(): StakingBalanceCollection
    {
        if (!$this->staking_balances instanceof StakingBalanceCollection) {
            $this->staking_balances = new StakingBalanceCollection($this->staking_balances->toArray());
        }
        return $this->staking_balances;
    }

    public function hasBalanceOf(Asset $asset): bool
    {
        $b = $this->getBalanceOf($asset);
        return $b && !$b->isZero();
    }

    public function getBalanceOf(Asset $asset): ?Balance
    {
        return $this->getSpotBalances()->findOfAsset($asset);
    }

    public function canTrade(): bool
    {
        return $this->canBuy() || $this->canSell();
    }

    public function canBuy(): bool
    {
        $quote_balance = $this->getSpotBalances()->findOneWithAssetSymbol($this->getQuoteSymbol());
        return $quote_balance && $quote_balance->getAmount() > $this->getSafetyAmount();
    }

    public function canSell(): bool
    {
        return $this->getSpotBalances()->filterCrypto()->filterNonZero()->count() > 0;
    }

    /** $price is how much Base can be bought with one Quote */
    public function canPlaceOrder(Order $order, float $price): bool
    {
        if ($order->isBuy()) {
            return $this->canPlaceBuyOrder($order->getPair(), $price * $order->getVolume());
        }
        return $this->canPlaceSellOrder($order->getPair(), $order->getVolume());
    }

    private function canPlaceBuyOrder(Pair $pair, float $quote_amount): bool
    {
        $quote_balance = $this->getSpotBalances()->findOneWithAssetSymbol($pair->getQuoteSymbol());
        if (!$quote_balance) {
            return false;
        }
        $available_amount = $quote_balance->getAmount();
        return $available_amount > $quote_amount;   // TODO poner un límite de seguridad??
    }

    private function canPlaceSellOrder(Pair $pair, float $base_amount): bool
    {
        $base_balance = $this->getSpotBalances()->findOneWithAssetSymbol($pair->getBaseSymbol());
        if (!$base_balance) {
            return false;
        }
        $available_amount = $base_balance->getAmount();
        return $available_amount >= $base_amount;
    }

    public function canStake(SpotAsset $asset, float $amount): bool
    {
        $balance = $this->getSpotBalances()->findOfAsset($asset);
        if (!$balance) {
            return false;
        }
        return ($balance->getAmount() - $amount) >= 1e-15;
    }

    public function canUnstake(StakingAsset $asset, float $amount): bool
    {
        $balance = $this->getStakingBalances()->findOfAsset($asset);
        if (!$balance) {
            return false;
        }
        return ($balance->getAmount() - $amount) >= 1e-15;
    }

    // Preferences
    private function getPreferences(): PreferenceCollection
    {
        if (!$this->preferences instanceof PreferenceCollection) {
            $this->preferences = new PreferenceCollection($this->preferences->toArray());
        }
        return $this->preferences;
    }

    public function updatePreferences(PreferenceCollection $preferences): void
    {
        foreach ($preferences as $preference) {
            /** @var Preference $preference */
            //$this->updatePreference($preference);
            $this->getPreferences()->add($preference);
        }
    }

    public function getQuoteSymbol(): string
    {
        return $this->getPreferences()->find(Preference::NAME_QUOTE_SYMBOL) ?? 'EUR';
    }

    public function getBuyStrategyName(): ?string
    {
        return $this->getPreferences()->find(Preference::NAME_BUY_STRATEGY);
    }

    public function getSellStrategyName(): ?string
    {
        return $this->getPreferences()->find(Preference::NAME_SELL_STRATEGY);
    }

    public function getSafetyAmount(): ?int
    {
        $value = $this->getPreferences()->find(Preference::NAME_SAFETY_AMOUNT);
        return $value ? (int)$value : null;
    }
}