<?php

namespace App\Domain\Model\Trading;

use App\Domain\Model\Asset\Pair;
use Doctrine\Common\Collections\ArrayCollection;

class CandleCollection extends ArrayCollection
{
    private Pair $pair;
    private int $timespan;


    public static function createFromRawData(Pair $pair, int $timespan = 0, array $elements = []): self
    {
        $candles = [];
        foreach ($elements as $raw) {
            $candles[] = Candle::create($pair, $timespan, $raw[0], (float)$raw[1], (float)$raw[2], (float)$raw[3], (float)$raw[4], (float)$raw[5], (int)$raw[7]);
        }
        return new self($pair, $timespan, $candles);
    }

    public function __construct(Pair $pair, int $timespan = 0, array $elements = [])
    {
        $this->pair = $pair;
        $this->timespan = $timespan;

        foreach ($elements as $element) {
            $this->checkValidity($element);
        }

        parent::__construct($elements);
    }

    private function checkValidity($element): void
    {
        if (!$element instanceof Candle) {
            throw new \InvalidArgumentException("All elements in a CandleCollection must be a Candle.");
        }
        if ($element->getTimespan() !== $this->timespan) {
            throw new \InvalidArgumentException("All Candles in a CandleCollection must have same timespan.");
        }
        if ($element->getPairSymbol() !== $this->pair->getSymbol()) {
            throw new \InvalidArgumentException("All Candles in a CandleCollection must be of the same Pair.");
        }
    }

    public function getPair(): Pair
    {
        return $this->pair;
    }

    public function getTimespan(): int
    {
        return $this->timespan;
    }

    public function fillGaps(): self
    {
        if ($this->count() < 2) { return $this; }

        $iterator = $this->getIterator();
        /** @var Candle $current */
        $current = $iterator->current();
        $iterator->next();
        /** @var Candle $next */
        $next = $iterator->current();

        $filled = new CandleCollection($this->pair, $this->timespan, [$current]);
        for ($i = 0; $i < $this->count()-1; $i++) {
            $intermediate_elements = (($next->getTimestamp() - $current->getTimestamp()) / (Candle::SECONDS_PER_MINUTE * $current->getTimespan())) - 1;
            for ($j=1; $j <= $intermediate_elements; $j++) {
                $weight_current = 1-($j/($intermediate_elements+1.0));
                $current = Candle::interpolate($weight_current, $current, $next);
                $filled->add($current);
            }
            $filled->add($next);
            $current = $next;
            $iterator->next();
            $next = $iterator->current();
        }
        return $filled;
    }

    public function increaseTimespan(int $timespan): self
    {
        if ($timespan === $this->timespan) {
            return $this;
        }
        if ($timespan < $this->timespan) {
            throw new \LogicException("Can't increase timespan from {$this->timespan} to $timespan.");
        }
        if ($timespan % $this->timespan !== 0) {
            throw new \LogicException("Timespan $timespan is not int-divisible by {$this->timespan}.");
        }

        // TODO Cuidado con:
        //  - El ultimo Candle no está completo por lo general
        //  - Puede que el nº de Candles no sea múltiple exacto, y hay que 'eliminar' los primeros.
        return $this;
    }

    public function filterLastCandles(int $num): self
    {
        return new self($this->pair, $this->timespan, array_slice($this->toArray(), -$num));
    }

    public function getPerformanceCollection(): PerformanceCollection
    {
        $performance_collection = new PerformanceCollection($this->pair);
        foreach ($this as $candle) {
            /** @var Candle $candle */
            $performance_collection->add(Performance::fromCandle($candle));
        }
        return $performance_collection;
    }



    public function getCompoundedPerformance(): Performance
    {
        return $this->getPerformanceCollection()->reduce();
    }

    public function getPerformance(): Performance
    {
        return Performance::fromCandleCollection($this);
    }

    public function getLastPrice(): float
    {
        return $this->last()->getClose();
    }
}