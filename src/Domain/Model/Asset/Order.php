<?php

namespace App\Domain\Model\Asset;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Shared\Amount\Amount;
use App\Domain\Model\Shared\TimeInForce\TimeInForce;

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

    protected Account $account;
    protected Pair $pair;
    protected string $type;       // kraken: ordertype    // TODO change to Enum when PHP8.1 is available!
    protected string $operation;                          // TODO change to Enum when PHP8.1 is available!
    protected Amount $price;
    protected Amount $price2;
    protected int $leverage;
    protected string $status;     // kraken: info obtained from getOrdersInfo     // TODO change to Enum when PHP8.1 is available!

    protected TimeInForce $time_in_force;

    protected string $txid;
    protected int $userref;
}