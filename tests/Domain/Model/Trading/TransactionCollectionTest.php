<?php

namespace Domain\Model\Trading;

use App\Domain\Model\Trading\SpotTransaction;
use App\Domain\Model\Trading\StakingTransaction;
use App\Domain\Model\Trading\TransactionCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class TransactionCollectionTest extends MockeryTestCase
{
    protected function setUp(): void
    {

    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testMethodFilterSpot(): void
    {
        $transactions = new TransactionCollection([
            m::namedMock('spot1', SpotTransaction::class),
            m::namedMock('staking1', StakingTransaction::class),
            m::namedMock('spot2', SpotTransaction::class),
        ]);

        $spot_transactions = $transactions->filterSpot();

        $this->assertCount(2, $spot_transactions);
        $this->assertContains( m::fetchMock('spot1'), $spot_transactions);
        $this->assertContains( m::fetchMock('spot2'), $spot_transactions);
    }

    public function testMethodFilterStaking(): void
    {
        $transactions = new TransactionCollection([
            m::namedMock('spot1', SpotTransaction::class),
            m::namedMock('staking1', StakingTransaction::class),
            m::namedMock('spot2', SpotTransaction::class),
        ]);

        $spot_transactions = $transactions->filterStaking();

        $this->assertCount(1, $spot_transactions);
        $this->assertContains( m::fetchMock('staking1'), $spot_transactions);
    }

    public function testMethodFilterOfAssetSymbol(): void
    {
        $transactions = new TransactionCollection([
            m::namedMock('tx1', SpotTransaction::class)->allows(['getAssetSymbol' => 'XBT']),
            m::namedMock('tx2', SpotTransaction::class)->allows(['getAssetSymbol' => 'EUR']),
            m::namedMock('tx3', StakingTransaction::class)->allows(['getAssetSymbol' => 'ALGO.S']),
            m::namedMock('tx4', SpotTransaction::class)->allows(['getAssetSymbol' => 'EUR']),
        ]);

        $eur_transactions = $transactions->filterOfAssetSymbol('EUR');


        $this->assertCount(2, $eur_transactions);
        $this->assertContains( m::fetchMock('tx2'), $eur_transactions);
        $this->assertContains( m::fetchMock('tx4'), $eur_transactions);
    }

}