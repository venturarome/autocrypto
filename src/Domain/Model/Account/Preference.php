<?php

namespace App\Domain\Model\Account;

class Preference
{
    public const NAME_BUY_STRATEGY = 'buy_strategy';
    public const NAME_QUOTE_SYMBOL = 'quote_symbol';
    public const NAME_SELL_STRATEGY = 'sell_strategy';
    public const NAME_SAFETY_AMOUNT = 'safety_amount';

    public const NAME_BUY_NUM_CANDLES = 'buy_num_candles';              // Num of candles on buy calculations
    public const NAME_BUY_RETURN_THRESHOLD = 'buy_return_threshold';    // Min return to execute buy
    public const NAME_BUY_MOMENTUM_RATIO = 'buy_momentum_ratio';        // Min momentum ratio to buy
    public const NAME_BUY_AMOUNT = 'buy_amount';                        // Amount of (quote) asset to buy
    public const NAME_SELL_NUM_CANDLES = 'sell_num_candles';            // Num of candles on sell calculations
    public const NAME_SELL_RETURN_THRESHOLD = 'sell_return_threshold';  // Max return to execute sell
    public const NAME_SELL_MOMENTUM_RATIO = 'sell_momentum_ratio';      // Max momentum ratio to sell
    public const NAME_SELL_AMOUNT = 'sell_amount';                      // Amount of (base) asset to sell
    public const NAME_SELL_LOCK_PERIOD = 'sell_lock_period';            // Minutes to lock sales after a buy.

    public const TYPE_BASIC = 'basic';
    public const TYPE_STRATEGY_PARAM = 'strategy_param';

    protected Account $account;
    protected string $name;
    protected string $value;
    protected string $type;

    public function __construct(string $name, string $value, string $type = self::TYPE_BASIC)
    {
        $this->name = $name;
        $this->value = $value;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isBuyStrategyParam(): bool
    {
        return $this->type === self::TYPE_STRATEGY_PARAM && str_starts_with($this->name, 'buy');
    }

    public function isSellStrategyParam(): bool
    {
        return $this->type === self::TYPE_STRATEGY_PARAM && str_starts_with($this->name, 'sell');
    }
}