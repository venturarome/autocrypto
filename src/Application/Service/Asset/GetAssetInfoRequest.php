<?php

namespace App\Application\Service\Asset;

// TODO poco uso! en vez de usar el cliente de kraken, deberíamos leer en nuestra BD!!
class GetAssetInfoRequest
{

    private string $asset;

    private string $aclass;


    public function __construct(string $asset = null, string $aclass = "currency")
    {
        $this->asset = $asset;
        $this->aclass = $aclass;
    }

    public function getAsset(): ?string
    {
        return $this->asset;
    }

    public function getAClass(): ?string
    {
        return $this->aclass;
    }
}