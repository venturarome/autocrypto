<?php

namespace Domain\Model\Trading;

use App\Domain\Mock\Trading\CandleMocker;
use App\Domain\Model\Trading\Candle;
use PHPUnit\Framework\TestCase;

class CandleTest extends TestCase
{
    public function testInterpolateMethod_HalfWay(): void
    {
        $candle1 = CandleMocker::create([
            'timestamp' => 1633046400, 'timespan' => 1,
            'open' => 10, 'high' => 13, 'low' => 7.5, 'close' => 9.5,
            'volume' => 150.5, 'trades' => 400
        ]);
        $candle2 = CandleMocker::create([
            'timestamp' => 1633046400, 'timespan' => 1,
            'open' => 15, 'high' => 16, 'low' => 5.5, 'close' => 7.5,
            'volume' => 250.5, 'trades' => 100
        ]);

        $newCandle = Candle::interpolate(0.5, $candle1, $candle2);

        $this->assertEquals(1, $newCandle->getTimespan());
        $this->assertEquals(1633046460, $newCandle->getTimestamp());
        $this->assertEqualsWithDelta(12.5, $newCandle->getOpen(), 1e-4);
        $this->assertEqualsWithDelta(14.5, $newCandle->getHigh(), 1e-4);
        $this->assertEqualsWithDelta(6.5, $newCandle->getLow(), 1e-4);
        $this->assertEqualsWithDelta(8.5, $newCandle->getClose(), 1e-4);
        $this->assertEqualsWithDelta(200.5, $newCandle->getVolume(), 1e-4);
        $this->assertEquals(250, $newCandle->getTrades());
    }

    public function testInterpolateMethod_AllFirstCandle(): void
    {
        $candle1 = CandleMocker::create([
            'timestamp' => 1633046400, 'timespan' => 1,
            'open' => 10, 'high' => 13, 'low' => 7.5, 'close' => 9.5,
            'volume' => 150.5, 'trades' => 400
        ]);
        $candle2 = CandleMocker::create([
            'timestamp' => 1633046400, 'timespan' => 1,
            'open' => 15, 'high' => 16, 'low' => 5.5, 'close' => 7.5,
            'volume' => 250.5, 'trades' => 100
        ]);

        $newCandle = Candle::interpolate(1, $candle1, $candle2);

        $this->assertEquals(1, $newCandle->getTimespan());
        $this->assertEquals(1633046460, $newCandle->getTimestamp());
        $this->assertEqualsWithDelta(10, $newCandle->getOpen(), 1e-4);
        $this->assertEqualsWithDelta(13, $newCandle->getHigh(), 1e-4);
        $this->assertEqualsWithDelta(7.5, $newCandle->getLow(), 1e-4);
        $this->assertEqualsWithDelta(9.5, $newCandle->getClose(), 1e-4);
        $this->assertEqualsWithDelta(150.5, $newCandle->getVolume(), 1e-4);
        $this->assertEquals(400, $newCandle->getTrades());
    }

    public function testInterpolateMethod_AllSecondCandle(): void
    {
        $candle1 = CandleMocker::create([
            'timestamp' => 1633046400, 'timespan' => 1,
            'open' => 10, 'high' => 13, 'low' => 7.5, 'close' => 9.5,
            'volume' => 150.5, 'trades' => 400
        ]);
        $candle2 = CandleMocker::create([
            'timestamp' => 1633046400, 'timespan' => 1,
            'open' => 15, 'high' => 16, 'low' => 5.5, 'close' => 7.5,
            'volume' => 250.5, 'trades' => 100
        ]);

        $newCandle = Candle::interpolate(0, $candle1, $candle2);

        $this->assertEquals(1, $newCandle->getTimespan());
        $this->assertEquals(1633046460, $newCandle->getTimestamp());
        $this->assertEqualsWithDelta(15, $newCandle->getOpen(), 1e-4);
        $this->assertEqualsWithDelta(16, $newCandle->getHigh(), 1e-4);
        $this->assertEqualsWithDelta(5.5, $newCandle->getLow(), 1e-4);
        $this->assertEqualsWithDelta(7.5, $newCandle->getClose(), 1e-4);
        $this->assertEqualsWithDelta(250.5, $newCandle->getVolume(), 1e-4);
        $this->assertEquals(100, $newCandle->getTrades());
    }

    public function testInterpolateMethod_BiggerTimespan(): void
    {
        $candle1 = CandleMocker::create(['timestamp' => 1633046400, 'timespan' => 5]);
        $candle2 = CandleMocker::create(['timespan' => 5]);

        $newCandle = Candle::interpolate(0.5, $candle1, $candle2);

        $this->assertEquals(5, $newCandle->getTimespan());
        $this->assertEquals(1633046700, $newCandle->getTimestamp());
    }
}