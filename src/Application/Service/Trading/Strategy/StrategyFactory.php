<?php

namespace App\Application\Service\Trading\Strategy;

use App\Application\Exception\NotFoundException;

class StrategyFactory
{
    public function createByName(string $name): Strategy
    {
        return match ($name) {
            BuyStepAmountStrategy::NAME => new BuyStepAmountStrategy(),
            BuyStepAllStrategy::NAME => new BuyStepAllStrategy(),
            BuyMomentumAllStrategy::NAME => new BuyMomentumAllStrategy(),
            BuyMomentumAmountStrategy::NAME => new BuyMomentumAmountStrategy(),
            BuyNullStrategy::NAME => new BuyNullStrategy(),
            SellStepAllStrategy::NAME => new SellStepAllStrategy(),
            SellNoLossesAllStrategy::NAME => new SellNoLossesAllStrategy(),
            SellAllWithBlockPeriodStrategy::NAME => new SellAllWithBlockPeriodStrategy(),
            SellNullStrategy::NAME => new SellNullStrategy(),
            default => throw new NotFoundException("Strategy with name '$name' not found by StrategyFactory!"),
        };
    }
}