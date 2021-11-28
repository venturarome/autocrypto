<?php

namespace App\Application\Service\Asset;

use App\Domain\Event\Asset\AssetCreated;
use App\Domain\Model\Asset\Asset;
use App\Domain\Model\Event\Event;
use App\Domain\Repository\Asset\AssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CreateAssetService
{
    private EntityManagerInterface $entity_manager;
    private AssetRepository $asset_repo;

    public function __construct(
        AssetRepository $asset_repo,
        EntityManagerInterface $entity_manager
    ) {
        $this->entity_manager = $entity_manager;
        $this->asset_repo = $asset_repo;
    }

    public function execute(CreateAssetRequest $request): void
    {
        $symbol = $request->getSymbol();

        if ($this->asset_repo->findBySymbol($symbol) !== null) {
            return;
        }

        if ($this->shouldBeExcluded($symbol)) {
            return;
        }

        $asset = Asset::create(
            $symbol,
            $request->getName(),
            $request->getDecimals(),
            $request->getDisplayDecimals(),
            $this->guessType($request->getExtendedSymbol())
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

    private function shouldBeExcluded(string $symbol): bool
    {
        return count(explode('.', $symbol)) > 1;
    }

    private function guessType(string $extended_symbol): string
    {
        if (strlen($extended_symbol) === 4 && $extended_symbol[0] === 'Z') {
            return Asset::TYPE_FIAT;
        }
        if ($extended_symbol === 'CHF') {
            return Asset::TYPE_FIAT;
        }
        return Asset::TYPE_CRYPTO;
    }
}