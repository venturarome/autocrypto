<?php

namespace Application\Service\Trading\Strategy;

use App\Application\Service\Trading\Strategy\SellNoLossesAllStrategy;
use App\Domain\Model\Account\Account;
use App\Domain\Model\Account\Balance;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Model\Trading\CandleCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;


class SellNoLossesAllStrategyTest extends MockeryTestCase
{

    public function testOrderNotCreatedIfAccountDoesNotHaveBalanceOfThatAsset(): void
    {
        $account = m::mock(Account::class)->allows(['hasBalanceOf' => false]);
        $candles = m::namedMock('candles', CandleCollection::class)->allows([
            'getBase' => m::mock(SpotAsset::class),
            'filterLastCandles' => m::fetchMock('candles')
        ]);

        $order = (new SellNoLossesAllStrategy())->run($account, $candles);

        $this->assertNull($order);
    }

    public function testOrderNotCreatedIfCandlesPercentageReturnIsGoodAndCandlesLastPriceIsGood(): void
    {
        $base = m::mock(SpotAsset::class);
        $candles = m::mock(CandleCollection::class)->allows([
            'getBase' => $base,
            'filterLastCandles' => m::self(),
            'getPercentageReturn' => 2.5,
            'getLastPrice' => 120,
        ]);

        $balance = m::mock(Balance::class)->allows([
            'getAveragePrice' => 100,
        ]);
        $account = m::mock(Account::class)->allows([
            'hasBalanceOf' => true,
            'getBalanceOf' => $balance,
        ]);

        $order = (new SellNoLossesAllStrategy())->run($account, $candles);

        $this->assertNull($order);
    }

    public function testOrderCreatedIfCandlesPercentageReturnIsBadAndCandlesLastPriceIsGood(): void
    {
        $base = m::mock(SpotAsset::class);
        $pair = m::mock(Pair::class);
        $candles = m::mock(CandleCollection::class)->allows([
            'getBase' => $base,
            'filterLastCandles' => m::self(),
            'getPercentageReturn' => -2.5,
            'getLastPrice' => 120,
            'getPair' => $pair,
        ]);

        $balance = m::mock(Balance::class)->allows([
            'getAveragePrice' => 100,
            'getAmount' => 5,
        ]);
        $account = m::mock(Account::class)->allows([
            'hasBalanceOf' => true,
            'getBalanceOf' => $balance,
        ]);

        $order = (new SellNoLossesAllStrategy())->run($account, $candles);

        $this->assertNotNull($order);
    }

    public function testOrderCreatedIfCandlesPercentageReturnIsGoodAndCandlesLastPriceIsBad(): void
    {
        $base = m::mock(SpotAsset::class);
        $pair = m::mock(Pair::class);
        $candles = m::mock(CandleCollection::class)->allows([
            'getBase' => $base,
            'filterLastCandles' => m::self(),
            'getPercentageReturn' => 2.5,
            'getLastPrice' => 80,
            'getPair' => $pair,
        ]);

        $balance = m::mock(Balance::class)->allows([
            'getAveragePrice' => 100,
            'getAmount' => 5,
        ]);
        $account = m::mock(Account::class)->allows([
            'hasBalanceOf' => true,
            'getBalanceOf' => $balance,
        ]);

        $order = (new SellNoLossesAllStrategy())->run($account, $candles);

        $this->assertNotNull($order);
    }

    public function testOrderCreatedIfCandlesPercentageReturnIsBadAndCandlesLastPriceIsBad(): void
    {
        $base = m::mock(SpotAsset::class);
        $pair = m::mock(Pair::class);
        $candles = m::mock(CandleCollection::class)->allows([
            'getBase' => $base,
            'filterLastCandles' => m::self(),
            'getPercentageReturn' => -2.5,
            'getLastPrice' => 80,
            'getPair' => $pair,
        ]);

        $balance = m::mock(Balance::class)->allows([
            'getAveragePrice' => 100,
            'getAmount' => 5,
        ]);
        $account = m::mock(Account::class)->allows([
            'hasBalanceOf' => true,
            'getBalanceOf' => $balance,
        ]);

        $order = (new SellNoLossesAllStrategy())->run($account, $candles);

        $this->assertNotNull($order);
    }
}