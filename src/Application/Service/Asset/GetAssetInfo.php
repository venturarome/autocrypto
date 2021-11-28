<?php

namespace App\Application\Service\Asset;

use App\Infrastructure\Provider\Kraken\KrakenApiClient;

class GetAssetInfo
{

    protected KrakenApiClient $kraken_api_client;

    public function __construct(
        KrakenApiClient $kraken_api_client
    ) {
        $this->kraken_api_client = $kraken_api_client;
    }

    public function execute(GetAssetInfoRequest $request)
    {

        $assets_info = $this->kraken_api_client->getAssetInfo();
        //TODO probar a usar serializers!!

        return $assets_info['result'];

    }
}