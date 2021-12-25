<?php

namespace App\Application\Service\Asset;

use App\Domain\Event\Asset\AssetCreated;
use App\Domain\Model\Asset\SpotAsset;
use App\Domain\Model\Event\Event;
use App\Domain\Repository\Asset\SpotAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CreateSpotAssetService
{
    private EntityManagerInterface $entity_manager;
    private SpotAssetRepository $asset_repo;

    public function __construct(
        SpotAssetRepository $asset_repo,
        EntityManagerInterface $entity_manager
    ) {
        $this->entity_manager = $entity_manager;
        $this->asset_repo = $asset_repo;
    }

    public function execute(CreateSpotAssetRequest $request): void
    {
        $symbol = $request->getSymbol();

        if ($this->asset_repo->findBySymbol($symbol) !== null) {
            return;
        }

        if ($this->isStakingAsset($symbol)) { // StakingAssets can be populated with other Command-service
            return;
        }

        $asset = SpotAsset::create(
            $symbol,
            $request->getName(),
            $request->getDecimals(),
            $request->getDisplayDecimals(),
            $this->guessSpotAssetSubtype($request->getExtendedSymbol())
        );
        $event = Event::createFrom(AssetCreated::raise($asset));

        try {
            $this->entity_manager->beginTransaction();  // suspend auto-commit
            $this->entity_manager->persist($asset);
            $this->entity_manager->persist($event);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        } catch (Exception $e) {
            $this->entity_manager->rollBack();
            throw $e;
        }
    }

    private function isStakingAsset(string $symbol): bool
    {
        return count(explode('.', $symbol)) > 1;
    }

    private function guessSpotAssetSubtype(string $extended_symbol): string
    {
        if (strlen($extended_symbol) === 4 && $extended_symbol[0] === 'Z') {
            return SpotAsset::TYPE_FIAT;
        }
        if ($extended_symbol === 'CHF') {
            return SpotAsset::TYPE_FIAT;
        }
        return SpotAsset::TYPE_CRYPTO;
    }
}