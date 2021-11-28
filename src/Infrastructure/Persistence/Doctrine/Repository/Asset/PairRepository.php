<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Asset\Pair;
use App\Domain\Repository\Asset\PairRepository as PairRepositoryI;
use App\Infrastructure\Persistence\Doctrine\Repository\RepositoryBase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class PairRepository extends ServiceEntityRepository /*RepositoryBase*/ implements PairRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pair::class);
    }

    public function findBySymbol(string $symbol): ?Pair
    {
        return $this->findOneBy(['symbol' => $symbol]);
    }

    public function findBySymbolOrFail(string $symbol): Pair
    {
        $pair = $this->findBySymbol($symbol);
        if (!$pair) {
            throw new NotFoundException("Pair with symbol '$symbol' not found!");
        }
        return $pair;
    }

    public function findByAssets(Asset $base, Asset $quote): ?Pair
    {
        return $this->findOneBy(['base_asset' => $base, 'quote_asset']);
    }

    public function findByAssetsOrFail(Asset $base, Asset $quote): Pair
    {
        $pair = $this->findByAssets($base, $quote);
        if (!$pair) {
            throw new NotFoundException("Pair of assets '{$base->getSymbol()}/{$quote->getSymbol()}' not found!");
        }
        return $pair;
    }
}