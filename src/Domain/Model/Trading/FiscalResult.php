<?php

namespace App\Domain\Model\Trading;


class FiscalResult
{
    protected array $current_purchases;   // ['timestamp' => ['amount', 'price']]
    protected float $total_amount;        // amount of crypto
    protected float $average_price;
    protected float $realized_pl;


    public function __construct(SpotTransactionCollection $transactions = null)
    {
        $this->current_purchases = [];
        $this->total_amount = 0;
        $this->average_price = 0;
        $this->realized_pl = 0;

        if ($transactions) {
            foreach ($transactions as $transaction) {
                /** @var SpotTransaction $transaction */
                $this->add($transaction);
            }
        }
    }

    private function add(SpotTransaction $transaction): void
    {
        if (!$transaction->isFromCryptoAsset()) {
            throw new \DomainException("FiscalResult can be calculated only for 'crypto' assets.");
        }

        $amount = $transaction->getAmount();
        $price = $transaction->getPrice();

        if ($amount > 0) {
            $this->average_price = ($this->total_amount*$this->average_price + $amount*$price) / ($this->total_amount + $amount);
            $this->total_amount += $amount;

            $this->current_purchases[$transaction->getTimestamp()] = [
                'amount' => $amount,
                'price' => $price
            ];
            ksort($this->current_purchases);
        }
        else {
            $amount = abs($amount);

            if ($amount > $this->total_amount) {
                throw new \DomainException("Can't subtract $amount to {$this->total_amount}.");
            }

            while ($amount > 0) {
                reset($this->current_purchases);
                $timestamp = key($this->current_purchases);
                $current = current($this->current_purchases);

                if ($amount <= $current['amount']) {
                    $this->realized_pl += ($price - $current['price']) * $amount;
                    $this->average_price = ($this->total_amount*$this->average_price - $amount*$current['price']) / ($this->total_amount - $amount);
                    $this->total_amount -= $amount;

                    $this->current_purchases[$timestamp]['amount'] -= $amount;
                    $amount = 0;
                }
                else {  // $amount > $current['amount']

                    $this->realized_pl += ($price - $current['price']) * $current['amount'];
                    $this->average_price = ($this->total_amount*$this->average_price - $current['amount']*$current['price']) / ($this->total_amount - $current['amount']);
                    $this->total_amount -= $current['amount'];

                    array_shift($this->current_purchases);
                    $amount -= $current['amount'];
                }
            }
        }
    }

    public function getTotalAmount(): float
    {
        return $this->total_amount;
    }

    public function getAveragePrice(): float
    {
        return $this->average_price;
    }

    public function getRealizedPL(): float
    {
        return $this->realized_pl;
    }

    public function calculatePLForAmountAndPrice(float $amount, float $price): float
    {
        if ($amount < 0) {
            throw new \DomainException("Invalid negative amount.");
        }
        if ($price < 0) {
            throw new \DomainException("Invalid negative price.");
        }
        if ($amount > $this->total_amount) {
            throw new \DomainException("Can't subtract $amount to {$this->total_amount}.");
        }

        $expected_pl = 0;

        reset($this->current_purchases);
        while (true) {
            $current = current($this->current_purchases);

            if ($amount <= $current['amount']) {
                $expected_pl += ($price - $current['price']) * $amount;
                break;
            }

            $expected_pl += ($price - $current['price']) * $current['amount'];
            $amount -= $current['amount'];
            next($this->current_purchases);
        }

        return $expected_pl;
    }
}