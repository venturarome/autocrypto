<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Account;

use App\Domain\Model\Account\StakingBalance;
use App\Domain\Repository\Account\StakingBalanceRepository as StakingBalanceRepositoryI;
use Doctrine\Persistence\ManagerRegistry;


class StakingBalanceRepository extends BalanceRepository implements StakingBalanceRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StakingBalance::class);
    }

}