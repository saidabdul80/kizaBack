<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateToResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency_to' => [
                'code' => $this->currencyTo->code,
                'name' => $this->currencyTo->name,
                'symbol' => $this->currencyTo->symbol,
                'flag' => $this->currencyTo->flag,
            ],
            'rate' => $this->rate,
            'is_active' => $this->is_active,
        ];
    }
}
