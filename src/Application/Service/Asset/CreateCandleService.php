<?php

namespace App\Application\Service\Asset;

use App\Domain\Model\Trading\Candle;
use App\Domain\Repository\Asset\PairRepository;
use App\Domain\Repository\Trading\CandleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;


class CreateCandleService
{
    private EntityManagerInterface $entity_manager;
    private PairRepository $pair_repo;
    private CandleRepository $candle_repo;

    public function __construct(
        CandleRepository $candle_repo,
        PairRepository $pair_repo,
        EntityManagerInterface $entity_manager
    ) {
        $this->entity_manager = $entity_manager;
        $this->pair_repo = $pair_repo;
        $this->candle_repo = $candle_repo;
    }

    public function execute(CreateCandleRequest $request): void
    {
        $pair = $this->pair_repo->findBySymbolOrFail($request->getPairSymbol());
        $timespan = $request->getTimespan();
        $timestamp = $request->getTimestamp();
        if ($this->candle_repo->findOneByPairTimespanAndTimestamp($pair, $timespan, $timestamp) !== null) {
            return;
        }

        $candle = Candle::create(
            $pair,
            $timespan,
            $timestamp,
            (float)$request->getOpen(),
            (float)$request->getHigh(),
            (float)$request->getLow(),
            (float)$request->getClose(),
            (float)$request->getVolume(),
            $request->getTrades()
        );

        try {
            $this->entity_manager->beginTransaction();  // suspend auto-commit
            $this->entity_manager->persist($candle);
            $this->entity_manager->flush();
            $this->entity_manager->commit();
            $this->entity_manager->clear(get_class($candle));
        } catch (Exception $e) {
            $this->entity_manager->rollBack();
            throw $e;
        }
    }
}