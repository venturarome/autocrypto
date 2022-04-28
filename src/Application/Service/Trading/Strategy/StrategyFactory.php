<?php

namespace App\Application\Service\Trading\Strategy;

use App\Application\Exception\NotFoundException;
use App\Domain\Model\Account\Account;

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
            SellAllWithLockPeriodStrategy::NAME => new SellAllWithLockPeriodStrategy(),
            SellMomentumAllStrategy::NAME => new SellMomentumAllStrategy(),
            SellNullStrategy::NAME => new SellNullStrategy(),
            default => throw new NotFoundException("Strategy with name '$name' not found by StrategyFactory!"),
        };
    }

    public function createCustomBuyForAccount(Account $account): Strategy
    {
        return $this->createCustom($account->getBuyStrategyName(), $account->getBuyStrategyParams());
    }

    public function createCustomSellForAccount(Account $account): Strategy
    {
        return $this->createCustom($account->getSellStrategyName(), $account->getSellStrategyParams());
    }

    private function createCustom(string $name, array $params): Strategy
    {
        return match ($name) {
            BuyStepAmountStrategy::NAME => new BuyStepAmountStrategy($params),
            BuyStepAllStrategy::NAME => new BuyStepAllStrategy($params),
            BuyMomentumAllStrategy::NAME => new BuyMomentumAllStrategy($params),
            BuyMomentumAmountStrategy::NAME => new BuyMomentumAmountStrategy($params),
            BuyNullStrategy::NAME => new BuyNullStrategy(),
            SellStepAllStrategy::NAME => new SellStepAllStrategy($params),
            SellNoLossesAllStrategy::NAME => new SellNoLossesAllStrategy($params),
            SellAllWithLockPeriodStrategy::NAME => new SellAllWithLockPeriodStrategy($params),
            SellMomentumAllStrategy::NAME => new SellMomentumAllStrategy($params),
            SellNullStrategy::NAME => new SellNullStrategy(),
            default => throw new NotFoundException("Strategy with name '$name' not found by StrategyFactory!"),
        };
    }
}