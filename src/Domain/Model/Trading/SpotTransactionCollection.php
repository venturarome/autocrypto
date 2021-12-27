<?php

namespace App\Domain\Model\Trading;


class SpotTransactionCollection extends TransactionCollection
{
    public function getFiscalResultOfAssetSymbol(string $asset_symbol): FiscalResult
    {
        return new FiscalResult($this->filterOfAssetSymbol($asset_symbol));
    }

    public function getFiscalResult(): FiscalResult
    {
        return new FiscalResult($this);
    }
}