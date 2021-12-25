<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Account;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\SpotBalance;
use App\Domain\Repository\Account\SpotBalanceRepository as SpotBalanceRepositoryI;
use Doctrine\Persistence\ManagerRegistry;


class SpotBalanceRepository extends BalanceRepository implements SpotBalanceRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotBalance::class);
    }

    public function findFiatOfAccount(Account $account): array
    {
        // TODO: Implement findFiatOfAccount() method.
    }
}