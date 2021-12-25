<?php

namespace App\Application\Service\Asset;


class CreateStakingAssetRequest
{
    private string $symbol;
    private string $asset_symbol;
    private float $min_reward;
    private float $max_reward;
    private float $min_staking;
    private float $min_unstaking;
    private bool $on_chain;
    private bool $can_stake;
    private bool $can_unstake;
    private string $method;

    public function __construct(
        string $symbol,
        string $asset_symbol,
        float $min_reward,
        float $max_reward,
        float $min_staking,
        float $min_unstaking,
        bool $on_chain,
        bool $can_stake,
        bool $can_unstake,
        string $method
    ) {
        $this->symbol = $symbol;
        $this->asset_symbol = $asset_symbol;
        $this->min_reward = $min_reward;
        $this->max_reward = $max_reward;
        $this->min_staking = $min_staking;
        $this->min_unstaking = $min_unstaking;
        $this->on_chain = $on_chain;
        $this->can_stake = $can_stake;
        $this->can_unstake = $can_unstake;
        $this->method = $method;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAssetSymbol(): string
    {
        return $this->asset_symbol;
    }

    public function getMinReward(): float
    {
        return $this->min_reward;
    }

    public function getMaxReward(): float
    {
        return $this->max_reward;
    }

    public function getMinStaking(): float
    {
        return $this->min_staking;
    }

    public function getMinUnstaking(): float
    {
        return $this->min_unstaking;
    }

    public function onChain(): bool
    {
        return $this->on_chain;
    }

    public function canStake(): bool
    {
        return $this->can_stake;
    }

    public function canUnstake(): bool
    {
        return $this->can_unstake;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}