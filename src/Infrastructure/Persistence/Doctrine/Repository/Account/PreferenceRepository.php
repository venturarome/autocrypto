<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Account;

use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Preference;
use App\Domain\Model\Account\PreferenceCollection;
use App\Domain\Repository\Account\PreferenceRepository as PreferenceRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class PreferenceRepository extends ServiceEntityRepository implements PreferenceRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preference::class);
    }


    public function findOfAccount(Account $account): PreferenceCollection
    {
        // TODO: Implement findOfAccount() method.
    }

    public function findOfAccountByName(Account $account, string $name): Preference
    {
        // TODO: Implement findOfAccountByName() method.
    }
}