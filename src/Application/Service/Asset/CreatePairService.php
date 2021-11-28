<?php

namespace App\Application\Service\Asset;

use App\Domain\Event\Asset\PairCreated;
use App\Domain\Model\Asset\Leverage;
use App\Domain\Model\Asset\LeverageCollection;
use App\Domain\Model\Asset\Pair;
use App\Domain\Model\Event\Event;
use App\Domain\Model\Shared\Amount\Amount;
use App\Domain\Repository\Asset\AssetRepository;
use App\Domain\Repository\Asset\PairRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreatePairService
{
    private PairRepository $pair_repo;
    private AssetRepository $asset_repo;
    private EntityManagerInterface $entity_manager;

    public function __construct(
        PairRepository $pair_repo,
        AssetRepository $asset_repo,
        EntityManagerInterface $entity_manager
    ) {
        $this->pair_repo = $pair_repo;
        $this->asset_repo = $asset_repo;
        $this->entity_manager = $entity_manager;
    }

    public function execute(CreatePairRequest $request): void
    {
        $symbol = $request->getSymbol();

        if ($this->pair_repo->findBySymbol($symbol) !== null) {
            return;
        }

        if ($this->shouldBeExcluded($symbol)) {
            return;
        }

        $base = $this->asset_repo->findBySymbolOrFail($request->getBase());
        $quote = $this->asset_repo->findBySymbolOrFail($request->getQuote());

        $leverages = new LeverageCollection();
        foreach ($request->getBuyLeverages() as $value) {
            $leverages->add(Leverage::createBuy($value));
        }
        foreach ($request->getSellLeverages() as $value) {
            $leverages->add(Leverage::createSell($value));
        }

        $pair = Pair::create(
            $symbol,
            $base,
            $quote,
            $request->getDecimals(),
            $request->getVolDecimals(),
            Amount::fromString($request->getOrderMin()),
            $leverages
        );
        $event = Event::createFrom(PairCreated::raise($pair));

        try {
            $this->entity_manager->beginTransaction();  // suspend auto-commit
            $this->entity_manager->persist($pair);
            $this->entity_manager->persist($event);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
        } catch (\Exception $e) {
            $this->entity_manager->rollBack();
            throw $e;
        }
    }

    private function shouldBeExcluded(string $symbol): bool
    {
        return count(explode('.', $symbol)) > 1;
    }

}