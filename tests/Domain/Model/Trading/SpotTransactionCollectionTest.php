<?php

namespace Domain\Model\Trading;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class SpotTransactionCollectionTest extends MockeryTestCase
{
    protected function setUp(): void
    {

    }

    protected function tearDown(): void
    {
        m::close();
    }

}