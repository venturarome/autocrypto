<?php

namespace App\Presentation\Web\Controller;

use App\Infrastructure\Persistence\Doctrine\Repository\Asset\PairRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\Asset\SpotAssetRepository;
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

    // TODO seguir aquí
//    #[Route('/pairs', name: 'pairs', methods: ['GET'])]
//    public function pairs(PairRepository $pairRepo): Response
//    {
//        return $this->render('@Web/Asset/pairs.html.twig', [
//            'pairs' => $pairRepo->findAll(),
//        ]);
//    }
}
