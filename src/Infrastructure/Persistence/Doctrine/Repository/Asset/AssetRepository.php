<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Asset;
use App\Domain\Repository\Asset\AssetRepository as AssetRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class AssetRepository extends ServiceEntityRepository implements AssetRepositoryI
{
    public function __construct(ManagerRegistry $registry, string $entity_class = Asset::class)
    {
        parent::__construct($registry, $entity_class);
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