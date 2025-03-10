<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency_from' => [
                'code' => $this->currencyFrom->code,
                'name' => $this->currencyFrom->name,
                'symbol' => $this->currencyFrom->symbol,
                'flag' => $this->currencyFrom->flag,
            ],
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
