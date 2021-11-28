<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Asset;
use App\Domain\Repository\Asset\AssetRepository as AssetRepositoryI;
use App\Infrastructure\Persistence\Doctrine\Repository\RepositoryBase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class AssetRepository extends ServiceEntityRepository /*RepositoryBase*/ implements AssetRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    public function findBySymbol(string $symbol): ?Asset
    {
        return $this->findOneBy(['symbol' => $symbol]);
    }

    public function findBySymbolOrFail(string $symbol): Asset
    {
        $asset = $this->findBySymbol($symbol);
        if (!$asset) {
            throw new NotFoundException("Asset with symbol '$symbol' not found!");
        }
        return $asset;
    }
}