<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Trading;

use App\Domain\Model\Account\SpotBalance;
use App\Domain\Model\Trading\SpotTransaction;
use App\Domain\Model\Trading\SpotTransactionCollection;
use App\Domain\Repository\Trading\SpotTransactionRepository as SpotTransactionRepositoryI;
use Doctrine\Persistence\ManagerRegistry;


class SpotTransactionRepository extends TransactionRepository implements SpotTransactionRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpotTransaction::class);
    }

    public function findOfBalance(SpotBalance $balance)
    {
        // TODO: Implement findOfBalance() method.
    }

    public function findByOperationReference(string $operation_reference): SpotTransactionCollection
    {
        return new SpotTransactionCollection($this->findBy(['operation_reference' => $operation_reference]));
    }
}