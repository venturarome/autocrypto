<?php

namespace App\Presentation\Web\Controller;

use App\Application\Service\Asset\GetAssetInfo;
use App\Application\Service\Asset\GetAssetInfoRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CryptocurrencyController extends AbstractController
{
    #[Route('/cryptocurrency', name: 'cryptocurrency', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('@Web/Cryptocurrency/index.html.twig', [
            'controller_name' => 'CryptocurrencyController',
        ]);
    }

    #[Route('/test/{msg}', name: 'test', methods: ['GET'])]
    public function test(GetAssetInfo $get_asset_info, string $msg): Response
    {
        // TODO crear y usar command_bus

        // $assets_info = $this->get('autocrypto.service.get_asset_info')->execute(new GetAssetInfoRequest());
        $assets_info = $get_asset_info->execute(new GetAssetInfoRequest());



        return $this->render('@Web/Cryptocurrency/test.html.twig', [
            'assets_info' => $assets_info,
            'msg' => $msg
        ]);
    }
}
