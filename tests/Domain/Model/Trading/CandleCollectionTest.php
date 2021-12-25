<?php

namespace Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use PHPUnit\Framework\TestCase;

class CandleCollectionTest extends TestCase
{
    public function testCreationFailsIfCandlesAreOfDifferentPair(): void
    {
//        $this->expectException(\InvalidArgumentException::class);
//
//        new CandleCollection(
//            new CandleMock::create()
//        );
//
//        // TODO y añadir mas tests.
//
    }

    public function testfillGapsOfContiguousCandles(): void
    {

    }
}