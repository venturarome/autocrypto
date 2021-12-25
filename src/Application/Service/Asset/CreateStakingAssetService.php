<?php

namespace App\Application\Service\Asset;

use App\Domain\Event\Asset\AssetCreated;
use App\Domain\Model\Asset\StakingAsset;
use App\Domain\Model\Event\Event;
use App\Domain\Repository\Asset\SpotAssetRepository;
use App\Domain\Repository\Asset\StakingAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;


class CreateStakingAssetService
{
    private EntityManagerInterface $entity_manager;
    private SpotAssetRepository $spot_asset_repo;
    private StakingAssetRepository $staking_asset_repo;

    public function __construct(
        StakingAssetRepository $staking_asset_repo,
        SpotAssetRepository $spot_asset_repo,
        EntityManagerInterface $entity_manager
    ) {
        $this->entity_manager = $entity_manager;
        $this->spot_asset_repo = $spot_asset_repo;
        $this->staking_asset_repo = $staking_asset_repo;
    }

    public function execute(CreateStakingAssetRequest $request): void
    {
        $symbol = $request->getSymbol();
        if ($this->staking_asset_repo->findBySymbol($symbol) !== null) {
            return;
        }

        $asset_symbol = $request->getAssetSymbol();
        $asset = $this->spot_asset_repo->findBySymbol($asset_symbol);
        if (!$asset) {
            return;
        }

        $staking_asset = StakingAsset::create(
            $symbol,
            $asset,
            $request->getMinReward(),
            $request->getMaxReward(),
            $request->getMinStaking(),
            $request->getMinUnstaking(),
            $request->onChain(),
            $request->canStake(),
            $request->canUnstake(),
            $request->getMethod()
        );
        $event = Event::createFrom(AssetCreated::raise($staking_asset));

        try {
            $this->entity_manager->beginTransaction();  // suspend auto-commit
            $this->entity_manager->persist($staking_asset);
            $this->entity_manager->persist($event);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        } catch (Exception $e) {
            $this->entity_manager->rollBack();
            throw $e;
        }
    }
}