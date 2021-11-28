<?php

namespace App\Domain\Model\Shared\Amount;


class Amount
{
    private int $value;
    private int $decimals;

    public static function fromString(string $amount): self
    {
        $amount = rtrim($amount, '0');

        $value = (int)str_replace([',', '.'], '', $amount);
        if ($value === 0) {
            return static::zero();
        }

        $parts = explode('.', $amount);
        if (!in_array(count($parts), [1, 2], true)) {
            throw new \InvalidArgumentException();  // TODO customize message
        }

        if (count($parts) === 1) {
            return new self($value, 0);
        }

        [$int_part, $dec_part] = $parts;
        if ((int)$dec_part === 0) {
            return new self($int_part, 0);
        }

        return new self($value, strlen($dec_part));
    }

    public static function zero(): self
    {
        return new self(0,0);
    }

    private function __construct(int $value, int $decimals)
    {
        $this->value = $value;
        $this->decimals = $decimals;
    }

    // TODO should these methods be public? So far, need for testing
    public function getValue(): int
    {
        return $this->value;
    }
    public function getDecimals(): int
    {
        return $this->decimals;
    }

    public function toNumber(): int|float
    {
        return $this->decimals === 0
            ? $this->value
            : $this->value / 10**$this->decimals;
    }



    public function toString(): string
    {
        return (string)$this->toNumber();
    }

    public static function sum(Amount $a1, Amount $a2): Amount
    {
        return new Amount(
            ($a1->value * 10**$a2->decimals + $a2->value * 10**$a1->decimals) / 10**min($a1->decimals, $a2->decimals),
            max($a1->decimals, $a2->decimals)
        );
    }

    public static function subtract(Amount $a1, Amount $a2): Amount
    {
        return new Amount(
            ($a1->value * 10**$a2->decimals - $a2->value * 10**$a1->decimals) / 10**min($a1->decimals, $a2->decimals),
            max($a1->decimals, $a2->decimals)
        );
    }

    public static function equals(Amount $a1, Amount $a2): bool
    {
        return $a1->value === $a2->value && $a1->decimals === $a2->decimals;
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }
}