<?php

namespace App\Domain\Event\Asset;

use App\Domain\Event\ThrowableEvent;
use App\Domain\Model\Asset\Asset;

class AssetCreated extends ThrowableEvent
{
    protected const NAME = 'asset.asset.created';

    public static function raise(Asset $asset): self
    {
        return new self($asset);
    }

    private function __construct(Asset $asset)
    {
        $content = [
            'symbol' => $asset->getSymbol()
        ];

        parent::__construct($asset->getUuid(), $content);
    }



}