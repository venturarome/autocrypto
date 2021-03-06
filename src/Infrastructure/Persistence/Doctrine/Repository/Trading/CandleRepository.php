<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository\Trading;

use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Repository\Trading\CandleRepository as CandleRepositoryI;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class CandleRepository extends ServiceEntityRepository /*RepositoryBase*/ implements CandleRepositoryI
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candle::class);
    }

    public function findOneByPairTimespanAndTimestamp(Pair $pair, int $timespan, int $timestamp): ?Candle
    {
        return $this->findOneBy(['pair' => $pair, 'timespan' => $timespan, 'timestamp' => $timestamp]);
    }

    public function findForPairInRange(Pair $pair, int $timespan, \DateTimeInterface $date_from, \DateTimeInterface $date_to, int $first_result = null, int $max_results = null): CandleCollection
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.pair = :pair')
            ->andWhere('c.timespan = :timespan')
            ->andWhere('c.timestamp >= :timestamp_from')
            ->andWhere('c.timestamp <= :timestamp_to')
            ->setParameters([
                'pair' => $pair,
                'timespan' => $timespan,
                'timestamp_from' => $date_from->getTimestamp(),
                'timestamp_to' => $date_to->getTimestamp(),
            ])
            ->setFirstResult($first_result)
            ->setMaxResults($max_results);

        return new CandleCollection($pair, $timespan, $qb->getQuery()->getResult());
    }

    public function countForPairInRange(Pair $pair, int $timespan, \DateTimeInterface $date_from, \DateTimeInterface $date_to): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count (c)')
            ->where('c.pair = :pair')
            ->andWhere('c.timespan = :timespan')
            ->andWhere('c.timestamp >= :timestamp_from')
            ->andWhere('c.timestamp <= :timestamp_to')
            ->setParameters([
                'pair' => $pair,
                'timespan' => $timespan,
                'timestamp_from' => $date_from->getTimestamp(),
                'timestamp_to' => $date_to->getTimestamp(),
            ]);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function deleteForPairInRange(Pair $pair, int $timespan, \DateTimeInterface $date_from, \DateTimeInterface $date_to): void
    {
        $qb = $this->createQueryBuilder('c')
            ->delete(Candle::class, 'c')
            ->where('c.pair = :pair')
            ->andWhere('c.timespan = :timespan')
            ->andWhere('c.timestamp >= :timestamp_from')
            ->andWhere('c.timestamp <= :timestamp_to')
            ->setParameters([
                'pair' => $pair,
                'timespan' => $timespan,
                'timestamp_from' => $date_from->getTimestamp(),
                'timestamp_to' => $date_to->getTimestamp(),
            ]);

        $qb->getQuery()->execute();
    }
}