<?php

namespace App\Application\Service\Trading;


class UpdateTransactionsRequest
{
    private string $reference;

    public function __construct(string $reference)
    {
        $this->reference = $reference;
    }

    public function getReference(): string
    {
        return $this->reference;
    }
}