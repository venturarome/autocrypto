<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Trading;

use App\Domain\Model\Account\StakingBalance;
use App\Domain\Model\Trading\StakingTransaction;
use App\Domain\Repository\Trading\StakingTransactionRepository as StakingTransactionRepositoryI;
use Doctrine\Persistence\ManagerRegistry;


class StakingTransactionRepository extends TransactionRepository implements StakingTransactionRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StakingTransaction::class);
    }

    public function findOfStakingBalance(StakingBalance $balance)
    {
        // TODO: Implement findOfBalance() method.
    }
}