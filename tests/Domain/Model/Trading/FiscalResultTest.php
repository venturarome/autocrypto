<?php

namespace Domain\Model\Trading;

use App\Domain\Model\Trading\FiscalResult;
use App\Domain\Model\Trading\SpotTransaction;
use App\Domain\Model\Trading\SpotTransactionCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;


class FiscalResultTest extends MockeryTestCase
{
    protected function setUp(): void
    {

    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testEmpty(): void
    {
        $fr = new FiscalResult();

        $this->assertEquals(0, $fr->getTotalAmount());
        $this->assertEquals(0, $fr->getAveragePrice());
        $this->assertEquals(0, $fr->getRealizedPL());
    }

    public function testTransactionFromFiatAssetThrowsException(): void
    {
        $transactions = new SpotTransactionCollection([
            m::namedMock('spot1', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => false,
            ]),
        ]);

        $this->expectException(\DomainException::class);

        $fr = new FiscalResult($transactions);
    }

    public function testCreationWithSingleBuyTransaction(): void
    {
        $transactions = new SpotTransactionCollection([
            m::namedMock('spot1', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => true,
                'getTimestamp' => 100,
                'getAmount' => 4.3,
                'getPrice' => 1.5,
            ]),
        ]);

        $fr = new FiscalResult($transactions);

        $this->assertEquals(4.3, $fr->getTotalAmount());
        $this->assertEquals(1.5, $fr->getAveragePrice());
        $this->assertEquals(0, $fr->getRealizedPL());
    }

    public function testCreationWithSingleSellTransactionThrowsError(): void
    {
        $transactions = new SpotTransactionCollection([
            m::namedMock('spot1', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => true,
                'getTimestamp' => 100,
                'getAmount' => -4.3,
                'getPrice' => 1.5,
            ]),
        ]);

        $this->expectException(\DomainException::class);

        $fr = new FiscalResult($transactions);
    }

    public function testBuyAndPartialSell(): void
    {
        $transactions = new SpotTransactionCollection([
            m::namedMock('spot1', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => true,
                'getTimestamp' => 100,
                'getAmount' => 5,
                'getPrice' => 1.5,
            ]),
            m::namedMock('spot2', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => true,
                'getTimestamp' => 101,
                'getAmount' => -2,
                'getPrice' => 6,
            ]),
        ]);

        $fr = new FiscalResult($transactions);

        $this->assertEquals(3, $fr->getTotalAmount());
        $this->assertEquals(1.5, $fr->getAveragePrice());
        $this->assertEquals(9, $fr->getRealizedPL());
    }

    public function testExpectedGainsWithNegativeAmountThrows(): void
    {
        $transactions = new SpotTransactionCollection([]);

        $this->expectException(\DomainException::class);

        $fr = new FiscalResult($transactions);
        $fr->calculatePLForAmountAndPrice(-1, 1);
    }

    public function testExpectedGainsWithNegativePriceThrows(): void
    {
        $transactions = new SpotTransactionCollection([]);

        $this->expectException(\DomainException::class);

        $fr = new FiscalResult($transactions);
        $fr->calculatePLForAmountAndPrice(1, -1);
    }

    public function testExpectedGainsWithTooBigAmountThrows(): void
    {
        $transactions = new SpotTransactionCollection([]);

        $this->expectException(\DomainException::class);

        $fr = new FiscalResult($transactions);
        $fr->calculatePLForAmountAndPrice(1, 1);
    }

    public function testExpectedGainsWithBuyAndSell(): void
    {
        $transactions = new SpotTransactionCollection([
            m::namedMock('spot1', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => true,
                'getTimestamp' => 100,
                'getAmount' => 5,
                'getPrice' => 1.5,
            ]),
            m::namedMock('spot2', SpotTransaction::class)->allows([
                'isFromCryptoAsset' => true,
                'getTimestamp' => 101,
                'getAmount' => -2,
                'getPrice' => 3.1,
            ]),
        ]);

        $fr = new FiscalResult($transactions);

        $this->assertEquals(0, $fr->calculatePLForAmountAndPrice(0, 0));
        $this->assertEquals(-1, $fr->calculatePLForAmountAndPrice(1, 0.5));
        $this->assertEquals(5.4, $fr->calculatePLForAmountAndPrice(2, 4.2));
    }


    // TODO continue adding more tests!!
}