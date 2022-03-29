<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Asset;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Asset\PairCollection;
use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Repository\Asset\PairRepository as PairRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class PairRepository extends ServiceEntityRepository implements PairRepositoryI
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

    public function findByAssets(SpotAsset $base, SpotAsset $quote): ?Pair
    {
        return $this->findOneBy(['base' => $base, 'quote' => $quote]);
    }

    public function findByAssetsOrFail(SpotAsset $base, SpotAsset $quote): Pair
    {
        $pair = $this->findByAssets($base, $quote);
        if (!$pair) {
            throw new NotFoundException("Pair of assets '{$base->getSymbol()}/{$quote->getSymbol()}' not found!");
        }
        return $pair;
    }

    public function findByAsset(SpotAsset $asset): PairCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.quote = :asset')
            ->orWhere('a.base = :asset')
            ->setParameter('asset', $asset)
        ;

        return new PairCollection($qb->getQuery()->getResult());
    }

    public function findByQuote(SpotAsset $quote): PairCollection
    {
        return new PairCollection($this->findBy(['quote' => $quote]));
    }
}