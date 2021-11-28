<?php

namespace App\Domain\Model\Shared\TimeInForce;

class TimeInForce
{

    protected string $time_in_force;  // kraken: timeinforce                      // TODO change to Enum when PHP8.1 is available!
    protected string $start_time;     // kraken: starttm
    protected string $expire_time;    // kraken: expiretm
}