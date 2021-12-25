<?php

namespace App\Domain\Repository\Account;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Account\PreferenceCollection;

interface PreferenceRepository
{
    public function findOfAccount(Account $account): PreferenceCollection;

    public function findOfAccountByName(Account $account, string $name): Preference;
}