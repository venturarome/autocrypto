<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Trading;

use App\Domain\Model\Trading\Transaction;
use App\Domain\Repository\Trading\TransactionRepository as TransactionRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class TransactionRepository extends ServiceEntityRepository implements TransactionRepositoryI
{
    public function __construct(ManagerRegistry $registry, string $entity_class = Transaction::class)
    {
        parent::__construct($registry, $entity_class);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return $this->findOneBy(['reference' => $reference]);
    }
}