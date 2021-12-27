<?php

namespace App\Domain\Model\Trading;


use App\Application\Exception\NotFoundException;
use App\Domain\Model\Asset\Pair;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;

class CandleMap extends ArrayCollection
{
    private int $timespan;      // Timespan of candles. All Candles must be of the same timespan.

    public function __construct(int $timespan, array $elements = [])
    {
        $this->timespan = $timespan;

        foreach ($elements as $element) {
            $this->checkValidity($element);
        }

        parent::__construct($elements);
    }

    private function checkValidity($element): void
    {
        if (!$element instanceof CandleCollection) {
            throw new \InvalidArgumentException("All elements in a CandleMap must be a CandleCollection.");
        }
        if ($element->getTimespan() !== $this->timespan) {
            throw new \InvalidArgumentException("All CandleCollections in a CandleMap must have same timespan.");
        }
    }

    public function getTimespan(): int
    {
        return $this->timespan;
    }

    public function add($element)
    {
        $this->checkValidity($element);
        parent::add($element);
    }

    public function fillGaps(): self
    {
        $performance_map = new self($this->timespan);
        foreach ($this as $candle_collection) {
            /** @var CandleCollection $candle_collection */
            $performance_map->add($candle_collection->fillGaps());
        }
        return $performance_map;
    }

    public function filterLastCandles(int $num): self
    {
        $performance_map = new self($this->timespan);
        foreach ($this as $candle_collection) {
            /** @var CandleCollection $candle_collection */
            $performance_map->add($candle_collection->filterLastCandles($num));
        }
        return $performance_map;
    }

    public function getPerformanceMap(): PerformanceMap
    {
        $performance_map = new PerformanceMap();
        foreach ($this as $candle_collection) {
            /** @var CandleCollection $candle_collection */
            $performance_map->add($candle_collection->getPerformanceCollection());
        }
        return $performance_map;
    }




    public function selectHighestPerformant(): ?CandleCollection
    {
        if ($this->count() === 0) { return null; }

        $selected = $this->first();
        $selected_performance = $selected->getPerformance();

        foreach ($this as $candle_collection) {
            /** @var CandleCollection $candle_collection */
            $candle_collection_performance = Performance::fromCandleCollection($candle_collection);
            if ($candle_collection_performance->hasHigherReturnThan($selected_performance)) {
                $selected = $candle_collection;
                $selected_performance = $candle_collection_performance;
            }
        }

        return $selected;
    }
    
    public function getLastPriceOf(Pair $pair): float
    {
        $candle_collection = $this->findByPair($pair);
        if (!$candle_collection) {
            throw new NotFoundException("Pair {$pair->getSymbol()} not found on CandleMap.");
        }
        return $candle_collection->getLastPrice();
    }

    public function findByPair(Pair $pair): ?CandleCollection
    {
        foreach ($this as $candle_collection) {
            /** @var CandleCollection $candle_collection */
            if ($candle_collection->getPair()->getSymbol() === $pair->getSymbol()) {
                return $candle_collection;
            }
        }
        return null;
    }
}