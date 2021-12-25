<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;
use Doctrine\Common\Collections\ArrayCollection;

class PerformanceCollection extends ArrayCollection
{
    protected Pair $pair;

    // Cache
    private ?float $return;

    public function __construct(Pair $pair, array $performances = [])
    {
        $this->pair = $pair;
        parent::__construct($performances);
    }

    public function reduce(): Performance
    {
        return new Performance(
            $this->pair,
            $this->getReturn(),
            $this->first()->getFrom(),
            $this->last()->getTo()
        );
    }

    private function getReturn(): float
    {
        if (!isset($this->return)) {
            if ($this->count() === 0) {
                $this->return = 0;
            }

            // This is compounded return.
            $this->return = 1;
            foreach ($this as $performance) {
                /** @var Performance $performance */
                $this->return *= $performance->getReturn();
            }

        }
        return $this->return;
    }
}