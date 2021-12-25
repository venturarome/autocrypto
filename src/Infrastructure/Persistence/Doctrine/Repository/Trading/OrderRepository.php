<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Trading;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Trading\Order;
use App\Domain\Model\Trading\OrderCollection;
use App\Domain\Repository\Trading\OrderRepository as OrderRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class OrderRepository extends ServiceEntityRepository implements OrderRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByTxid(string $txid): ?Order
    {
        // TODO: Implement findByTxid() method.
    }

    public function findByTxidOrFail(string $txid): Order
    {
        // TODO: Implement findByTxidOrFail() method.
    }

    public function findOfAccount(Account $account): OrderCollection
    {
        // TODO: Implement findOfAccount() method.
    }

    public function findUncheckedOfAccount(Account $account): OrderCollection
    {
        // TODO: Implement findUncheckedOfAccount() method.
    }

    public function findOpenOfAccount(Account $account): OrderCollection
    {
        // TODO: Implement findOpenOfAccount() method.
    }
}