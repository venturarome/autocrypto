<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Account;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Balance;
use App\Domain\Repository\Account\BalanceRepository as BalanceRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class BalanceRepository extends ServiceEntityRepository implements BalanceRepositoryI
{
    public function __construct(ManagerRegistry $registry, string $entity_class = Balance::class)
    {
        parent::__construct($registry, $entity_class);
    }


    public function findOfAccount(Account $account): array
    {
        // TODO: Implement findOfAccount() method.
    }
}