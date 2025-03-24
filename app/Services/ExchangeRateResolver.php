<?php

namespace App\Services;

use App\Models\ExchangeRate;

class ExchangeRateResolver
{
    public function rate($currencyFrom, $currencyTo)
    {
        return ExchangeRate::where('currency_id_from', $currencyFrom)
            ->where('currency_id_to', $currencyTo)
            ->value('rate');
    }
}
