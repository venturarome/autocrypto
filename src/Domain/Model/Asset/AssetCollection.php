<?php

namespace App\Domain\Model\Asset;

use Doctrine\Common\Collections\ArrayCollection;

class AssetCollection extends ArrayCollection
{
    public function getSymbolsArray(): array
    {
        return $this->map(static function (Asset $a) { return $a->getSymbol(); })->toArray();
    }
}