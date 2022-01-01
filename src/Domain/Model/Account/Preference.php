<?php

namespace App\Domain\Model\Account;

class Preference
{
    public const NAME_BUY_STRATEGY = 'buy_strategy';
    public const NAME_QUOTE_SYMBOL = 'quote_symbol';
    public const NAME_SELL_STRATEGY = 'sell_strategy';
    public const NAME_SAFETY_AMOUNT = 'safety_amount';

    protected Account $account;
    protected string $name;
    protected string $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}