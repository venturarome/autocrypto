<?php

namespace App\Domain\Event\Asset;

use App\Domain\Event\ThrowableEvent;
use App\Domain\Model\Asset\Pair;

class PairCreated extends ThrowableEvent
{
    protected const NAME = 'asset.pair.created';

    public static function raise(Pair $pair): self
    {
        return new self($pair);
    }

    private function __construct(Pair $pair)
    {
        $content = [
            'symbol' => $pair->getSymbol()
        ];

        parent::__construct($pair->getUuid(), $content);
    }



}