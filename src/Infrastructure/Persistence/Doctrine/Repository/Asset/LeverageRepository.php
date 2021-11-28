<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\Leverage;
use App\Domain\Model\Asset\Pair;
use App\Domain\Repository\Asset\LeverageRepository as LeverageRepositoryI;
use App\Infrastructure\Persistence\Doctrine\Repository\RepositoryBase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class LeverageRepository extends ServiceEntityRepository /*RepositoryBase*/ implements LeverageRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leverage::class);
    }

    public function findBySymbol(string $symbol): ?Pair
    {
        return $this->findOneBy(['symbol' => $symbol]);
    }

    public function findByAsset(Asset $asset): array
    {
        // TODO: Implement findByAsset() method.
    }

    public function findBuyByAsset(Asset $asset): array
    {
        // TODO: Implement findBuyByAsset() method.
    }
}