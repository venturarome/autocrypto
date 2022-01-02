<?php

namespace Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use App\Domain\Model\Trading\Performance;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PerformanceTest extends MockeryTestCase
{
    public function testCreationFromOneCandleWithZeroReturn(): void
    {
        m::namedMock('candle1', Candle::class)->allows([
            'getPair' => m::namedMock('pair', Pair::class),
            'getOpen' => 20,
            'getHigh' => 30,
            'getLow' => 10,
            'getClose' => 20,
            'getTimestamp' => 14_000_000,
            'getNextTimestamp' => 14_000_060,
        ]);

        $performance = Performance::fromCandle(m::fetchMock('candle1'));

        $this->assertEqualsWithDelta(1, $performance->getReturn(), 1e-10);
    }

    public function testCreationFromOneCandleWithPositiveReturn(): void
    {
        m::namedMock('candle1', Candle::class)->allows([
            'getPair' => m::namedMock('pair', Pair::class),
            'getOpen' => 20,
            'getHigh' => 30,
            'getLow' => 10,
            'getClose' => 30,
            'getTimestamp' => 14_000_000,
            'getNextTimestamp' => 14_000_060,
        ]);

        $performance = Performance::fromCandle(m::fetchMock('candle1'));

        $this->assertEqualsWithDelta(1.5, $performance->getReturn(), 1e-10);
    }

    public function testCreationFromOneCandleWithNegativeReturn(): void
    {
        m::namedMock('candle1', Candle::class)->allows([
            'getPair' => m::namedMock('pair', Pair::class),
            'getOpen' => 20,
            'getHigh' => 30,
            'getLow' => 10,
            'getClose' => 10,
            'getTimestamp' => 14_000_000,
            'getNextTimestamp' => 14_000_060,
        ]);

        $performance = Performance::fromCandle(m::fetchMock('candle1'));

        $this->assertEqualsWithDelta(0.5, $performance->getReturn(), 1e-10);
    }

    // TODO continue (seriously)
}