<?php

namespace App\Domain\Model\Shared\Amount;


class Amount
{
    private int $value;
    private int $decimals;

    public static function fromString(string $amount): self
    {
        if (str_contains($amount, ',') || str_contains($amount, '.')) {
            $amount = rtrim($amount, '0');
        }

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

    public static function fromNumber(int|float $amount): Amount
    {
        return self::fromString((string)$amount);
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

    public static function scale(Amount $a1, float $scale): Amount
    {
        // We have to round to avoid a huge amount of decimals that could cause Amount::value to exceep PHP_MAX_INT
        $initial_decimals = $a1->decimals;
        return self::fromNumber(round($scale * $a1->toNumber(), $initial_decimals));
    }

    public static function interpolate(float $weight1, Amount $a1, Amount $a2): Amount
    {
        if ($weight1 < 0 || $weight1 > 1) {
            throw new \InvalidArgumentException("Weight must be a value between 0 and 1");
        }

        return self::subtract($a2, self::scale(self::subtract($a2, $a1), $weight1));
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