<?php

namespace Domain\Model\Trading;

use App\Domain\Mock\Asset\PairMocker;
use App\Domain\Mock\Trading\CandleCollectionMocker;
use App\Domain\Mock\Trading\CandleMocker;
use App\Domain\Model\Trading\Candle;
use PHPUnit\Framework\TestCase;

class CandleCollectionTest extends TestCase
{
    public function testCheckValidityMethod_CreationFailsIfElementIsNotACandle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CandleCollectionMocker::create(['elements' => [
            PairMocker::create(['symbol' => 'SYMBOL1'])
        ]]);
    }

    public function testCheckValidityMethod_CreationFailsIfCandlesAreOfDifferentPair(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['pair' => PairMocker::create(['symbol' => 'SYMBOL1'])]),
            CandleMocker::create(['pair' => PairMocker::create(['symbol' => 'SYMBOL2'])]),
        ]]);
    }

    public function testCheckValidityMethod_CreationFailsIfCandlesAreOfDifferentTimespan(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['timespan' => 1]),
            CandleMocker::create(['timespan' => 5]),
        ]]);
    }

    public function testFillGapsMethod_OneGap(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['timestamp' => 1633046400, 'open' => 10, 'high' => 13, 'low' => 7.5, 'close' => 9.5]),
            CandleMocker::create(['timestamp' => 1633046520, 'open' => 12, 'high' => 15, 'low' => 9.5, 'close' => 11.5]),
        ]]);

        $candleCollection = $candleCollection->fillGaps();
        $this->assertCount(3, $candleCollection);

        /** @var Candle $newCandle */
        $newCandle = $candleCollection->get(1);
        $this->assertEquals(1633046460, $newCandle->getTimestamp());
    }

    public function testFillGapsMethod_TwoGaps(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['timestamp' => 1633046400, 'open' => 10, 'high' => 13, 'low' => 7.5, 'close' => 9.5]),
            CandleMocker::create(['timestamp' => 1633046580, 'open' => 12, 'high' => 15, 'low' => 9.5, 'close' => 11.5]),
        ]]);

        $candleCollection = $candleCollection->fillGaps();
        $this->assertCount(4, $candleCollection);

        /** @var Candle $newCandle1 */
        /** @var Candle $newCandle2 */
        $newCandle1 = $candleCollection->get(1);
        $newCandle2 = $candleCollection->get(2);
        $this->assertEquals(1633046460, $newCandle1->getTimestamp());
        $this->assertEquals(1633046520, $newCandle2->getTimestamp());
    }

    public function testFilterLastCandlesMethod(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['open' => 10]),
            CandleMocker::create(['open' => 15]),
            CandleMocker::create(['open' => 8]),
            CandleMocker::create(['open' => 7.5]),
            CandleMocker::create(['open' => 9.2]),
            CandleMocker::create(['open' => 16.3]),
        ]]);

        $filteredCollection = $candleCollection->filterLastCandles(3);

        $this->assertCount(3, $filteredCollection);
        $this->assertEqualsWithDelta(7.5, $filteredCollection->get(0)->getOpen(), 1e-4);
        $this->assertEqualsWithDelta(9.2, $filteredCollection->get(1)->getOpen(), 1e-4);
        $this->assertEqualsWithDelta(16.3, $filteredCollection->get(2)->getOpen(), 1e-4);
    }

    public function testGetPercentageReturnMethod(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['open' => 10]),
            CandleMocker::create(),
            CandleMocker::create(),
            CandleMocker::create(),
            CandleMocker::create(),
            CandleMocker::create(['close' => 16.3]),
        ]]);

        $this->assertEqualsWithDelta(63, $candleCollection->getPercentageReturn(), 1e-4);
    }

    public function testGetAverageCloseMethod(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['close' => 10]),
            CandleMocker::create(['close' => 15]),
            CandleMocker::create(['close' => 8]),
            CandleMocker::create(['close' => 7.5]),
            CandleMocker::create(['close' => 9.2]),
            CandleMocker::create(['close' => 16.3]),
        ]]);

        $this->assertEqualsWithDelta(11.0, $candleCollection->getAverageClose(), 13-4);
    }

    public function testGetAverageOpenMethod(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['open' => 10]),
            CandleMocker::create(['open' => 15]),
            CandleMocker::create(['open' => 8]),
            CandleMocker::create(['open' => 7.5]),
            CandleMocker::create(['open' => 9.2]),
            CandleMocker::create(['open' => 16.3]),
        ]]);

        $this->assertEqualsWithDelta(11.0, $candleCollection->getAverageOpen(), 13-4);
    }

    public function testGetAverageLowMethod(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['low' => 10]),
            CandleMocker::create(['low' => 15]),
            CandleMocker::create(['low' => 8]),
            CandleMocker::create(['low' => 7.5]),
            CandleMocker::create(['low' => 9.2]),
            CandleMocker::create(['low' => 16.3]),
        ]]);

        $this->assertEqualsWithDelta(11.0, $candleCollection->getAverageLow(), 13-4);
    }

    public function testGetAverageHighMethod(): void
    {
        $candleCollection = CandleCollectionMocker::create(['elements' => [
            CandleMocker::create(['high' => 10]),
            CandleMocker::create(['high' => 15]),
            CandleMocker::create(['high' => 8]),
            CandleMocker::create(['high' => 7.5]),
            CandleMocker::create(['high' => 9.2]),
            CandleMocker::create(['high' => 16.3]),
        ]]);

        $this->assertEqualsWithDelta(11.0, $candleCollection->getAverageHigh(), 13-4);
    }
}