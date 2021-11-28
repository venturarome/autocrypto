<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Account;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Account\Account;
use App\Domain\Repository\Account\AccountRepository as AccountRepositoryI;
use App\Infrastructure\Persistence\Doctrine\Repository\RepositoryBase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class AccountRepository extends ServiceEntityRepository /*RepositoryBase*/ implements AccountRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }


    public function findByReference(string $reference): ?Account
    {
        return $this->findOneBy(['reference' => $reference]);
    }

    public function findByReferenceOrFail(string $reference): Account
    {
        $account = $this->findByReference($reference);
        if (!$account) {
            throw new NotFoundException("Account with reference '$reference' not found!");
        }
        return $account;
    }
}