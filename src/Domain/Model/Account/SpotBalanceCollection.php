<?php

namespace App\Domain\Model\Account;

use App\Domain\Model\Asset\SpotAsset;


class SpotBalanceCollection extends BalanceCollection
{
    public function filterCrypto(): self
    {
        return $this->filter(static function (SpotBalance $balance) {
            return $balance->getSpotAsset()->getType() === SpotAsset::TYPE_CRYPTO;
        });
    }

    public function filterFiat(): self
    {
        return $this->filter(static function (SpotBalance $balance) {
            return $balance->getSpotAsset()->getType() === SpotAsset::TYPE_FIAT;
        });
    }


}