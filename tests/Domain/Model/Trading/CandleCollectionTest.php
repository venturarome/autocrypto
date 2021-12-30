<?php

namespace Domain\Model\Trading;

use Mockery\Adapter\Phpunit\MockeryTestCase;


class CandleCollectionTest extends MockeryTestCase
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