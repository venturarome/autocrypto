<?php

namespace App\Domain\Repository\Trading;

use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Asset\Pair;


interface CandleRepository
{
    public function findOneByPairTimespanAndTimestamp(Pair $pair, int $timespan, int $timestamp): ?Candle;

    public function findForPairInRange(Pair $pair, int $timespan, \DateTimeInterface $date_from, \DateTimeInterface $date_to, int $first_result, int $max_results): CandleCollection;

    public function countForPairInRange(Pair $pair, int $timespan, \DateTimeInterface $date_from, \DateTimeInterface $date_to): int;
}