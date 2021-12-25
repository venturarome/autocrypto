<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Asset\SpotAsset;
use Ramsey\Uuid\Uuid;

class Order
{
    // TODO change to Enum when PHP8.1 is available!
    public const TYPE_MARKET = 'market';
    public const TYPE_LIMIT = 'limit';
    public const TYPE_STOP_LOSS = 'stop-loss';
    public const TYPE_TAKE_PROFIT = 'take-profit';
    public const TYPE_STOP_LOSS_LIMIT = 'stop-loss-limit';
    public const TYPE_TAKE_PROFT_LIMIT = 'take-profit-limit';
    public const TYPE_SETTLE_POSITION = 'settle-position';

    // TODO change to Enum when PHP8.1 is available!
    public const OPERATION_BUY = 'buy';
    public const OPERATION_SELL = 'sell';

    // TODO change to Enum when PHP8.1 is available!
    public const STATUS_UNCHECKED = 'unchecked';    // Initial status
    public const STATUS_PENDING = 'pending';        // Order pending book entry
    public const STATUS_OPEN = 'open';              // Open order
    public const STATUS_CLOSED = 'closed';          // Closed order
    public const STATUS_CANCELED = 'canceled';      // Order canceled
    public const STATUS_EXPIRED = 'expired';        // Order expired

    protected int $id;
    protected string $uuid;
    protected Account $account;
    protected Pair $pair;
    protected string $type;       // kraken: ordertype    // TODO change to Enum when PHP8.1 is available!
    protected string $operation;                          // TODO change to Enum when PHP8.1 is available!
    protected float $volume;        // In terms of base asset.
    protected ?float $trigger_price;
    protected ?float $limit_price;
    //protected ?int $leverage;
    //protected string $status;     // kraken: info obtained from getOrdersInfo     // TODO change to Enum when PHP8.1 is available!
    // protected TimeInForce $time_in_force;  <-- So far, I will only create market orders!
    //protected string $reference;
    // protected int $userref;
    //protected DateTracker $date_tracker;


    public static function createMarketBuy(Account $account, Pair $pair, float $volume): self
    {
        return new self(
            $account,
            $pair,
            self::TYPE_MARKET,
            self::OPERATION_BUY,
            $volume
        );
    }

    public static function createMarketSell(Account $account, Pair $pair, float $volume): self
    {
        return new self(
            $account,
            $pair,
            self::TYPE_MARKET,
            self::OPERATION_SELL,
            $volume
        );
    }

    public function __construct(
        Account $account,
        Pair $pair,
        string $type,
        string $operation,
        float $volume,
        float $trigger_price = null,
        float $limit_price = null
    ) {
        $this->uuid = $this->uuid = Uuid::uuid6()->toString();
        $this->account = $account;
        $this->pair = $pair;
        $this->type = $type;
        $this->operation = $operation;
        $this->volume = $volume;
        $this->trigger_price = $trigger_price;
        $this->limit_price = $limit_price;
    }

    public function getPair(): Pair
    {
        return $this->pair;
    }

    public function getPairSymbol(): string
    {
        return $this->pair->getSymbol();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getQuoteAsset(): SpotAsset
    {
        return $this->pair->getQuote();
    }

    public function getBaseAsset(): SpotAsset
    {
        return $this->pair->getBase();
    }

    public function isBuy(): bool
    {
        return $this->operation === self::OPERATION_BUY;
    }

    public function getVolume(): float
    {
        return $this->volume;
    }
}