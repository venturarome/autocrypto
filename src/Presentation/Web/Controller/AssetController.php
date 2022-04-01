<?php

namespace App\Presentation\Web\Controller;

use App\Domain\Model\Trading\Candle;
use App\Domain\Model\Trading\CandleCollection;
use App\Infrastructure\Persistence\Doctrine\Repository\Asset\PairRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\Asset\SpotAssetRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\Trading\CandleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssetController extends AbstractController
{
    #[Route('/assets', name: 'assets', methods: ['GET'])]
    public function assets(SpotAssetRepository $spotAssetRepo): Response
    {
        return $this->render('@Web/Asset/assets.html.twig', [
            'assets' => $spotAssetRepo->findAll(),
        ]);
    }

    #[Route('/asset/{symbol}', name: 'asset', methods: ['GET'])]
    public function asset(SpotAssetRepository $spotAssetRepo, PairRepository $pairRepo, string $symbol): Response
    {
        $asset = $spotAssetRepo->findBySymbol($symbol);
        $pairs = $asset ? $pairRepo->findByAsset($asset) : null;

        return $this->render('@Web/Asset/asset.html.twig', [
            'symbol' => $symbol,
            'asset' => $asset,
            'pairs' => $pairs,
        ]);
    }

    #[Route('/pairs', name: 'pairs', methods: ['GET'])]
    public function pairs(PairRepository $pairRepo): Response
    {
        return $this->render('@Web/Asset/pairs.html.twig', [
            'pairs' => $pairRepo->findAll(),
        ]);
    }

    #[Route('/pair/{symbol}', name: 'pair', methods: ['GET'])]
    public function pair(PairRepository $pairRepo, CandleRepository $candleRepo, string $symbol): Response
    {
        $pair = $pairRepo->findBySymbolOrFail($symbol);


        for ($i = 6; $i >= 0; $i--) {
            $date_from = (new \DateTime('first day of '.$i.' months ago'))->setTime(0, 0);
            $date_to = (new \DateTime('last day of '.$i.' months ago'))->setTime(23, 59);
            $candles_per_month[$date_from->format('Y-m')] = $candleRepo->countForPairInRange($pair, 1, $date_from, $date_to);
        }

        return $this->render('@Web/Asset/pair.html.twig', [
            'symbol' => $symbol,
            'pair' => $pair,
            'candles' => $candles_per_month,
        ]);
    }
}
