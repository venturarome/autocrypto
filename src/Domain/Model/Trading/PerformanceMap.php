<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Shared\Amount\Amount;
use Doctrine\Common\Collections\ArrayCollection;

class PerformanceMap extends ArrayCollection
{
    public function selectHighestPerformantPair(): ?Pair
    {
        if ($this->count() === 0) {
            return null;
        }

        $it = $this->getIterator();
        /** @var Performance $best_performant */
        $best_performant = $it->current();
        $it->next();
        while ($it->valid()) {
            if (!$best_performant->hasHigherReturnThan($it->current())) {
                $best_performant = $it->current();
            }
        }
        return $best_performant->getPair();
    }
}