<?php

namespace App\Domain\Repository\Account;

use App\Domain\Model\Account\Account;

interface AccountRepository
{
    public function findByReference(string $reference): ?Account;
    public function findByReferenceOrFail(string $reference): Account;
}