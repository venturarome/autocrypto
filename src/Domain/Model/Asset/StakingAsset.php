<?php

namespace App\Domain\Model\Asset;


class StakingAsset extends Asset
{
    protected SpotAsset $spot_asset;
    protected float $min_reward;
    protected float $max_reward;
    protected float $min_staking;
    protected float $min_unstaking;
    protected bool $on_chain;
    protected bool $can_stake;
    protected bool $can_unstake;
    protected string $method;

    public static function create(
        string $symbol,
        SpotAsset $spot_asset,
        float $min_reward,
        float $max_reward,
        float $min_staking,
        float $min_unstaking,
        bool $on_chain,
        bool $can_stake,
        bool $can_unstake,
        string $method
    ): self
    {
        return new self($symbol, $spot_asset, $min_reward, $max_reward, $min_staking, $min_unstaking, $on_chain, $can_stake, $can_unstake, $method);
    }

    private function  __construct(
        string $symbol,
        SpotAsset $spot_asset,
        float $min_reward,
        float $max_reward,
        float $min_staking,
        float $min_unstaking,
        bool $on_chain,
        bool $can_stake,
        bool $can_unstake,
        string $method
    ) {
        parent::__construct($symbol, $spot_asset->getDecimals(), $spot_asset->getDisplayDecimals());

        $this->spot_asset = $spot_asset;
        $this->min_reward = $min_reward;
        $this->max_reward = $max_reward;
        $this->min_staking = $min_staking;
        $this->min_unstaking = $min_unstaking;
        $this->on_chain = $on_chain;
        $this->can_stake = $can_stake;
        $this->can_unstake = $can_unstake;
        $this->method = $method;
    }


    public function getSpotAsset(): SpotAsset
    {
        return $this->spot_asset;
    }

    public function getName(): ?string
    {
        return $this->spot_asset->getName();
    }

    public function getType(): ?string
    {
        return $this->spot_asset->getType();
    }

}