<?php

namespace App\Domain\Model\Asset;

use Doctrine\Common\Collections\ArrayCollection;

class LeverageCollection extends ArrayCollection
{
    public function assignTo(Pair $pair): self
    {
        $this->forAll(static function ($_, Leverage $l) use ($pair) { $l->assignTo($pair); return true; });
        return $this;
    }

    public function filterBuy(): self
    {
        return $this->filter(static function (Leverage $l) { return $l->isBuy(); });
    }

    public function filterSell(): self
    {
        return $this->filter(static function (Leverage $l) { return $l->isSell(); });
    }
}