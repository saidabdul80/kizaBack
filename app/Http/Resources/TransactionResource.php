<?php
namespace App\Http\Resources;

use App\Enums\Methods;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'customer'      => $this->customer,
            'amount'        => $this->amount,
            'deposit_amount'=> $this->amount . $this->currencyFrom->code,
            'recipient_receiving_amount' => $this->total_amount . $this->currencyTo->code,
            'currency_from' => $this->currencyFrom,
            'currency_to'   => $this->currencyTo,
            'recipient'     => $this->recipient_details, // Handles both saved and unsaved recipients
            'type'          => $this->type,
            'rate'          => $this->rate,
            'fees'          => $this->fees,
            'method'        => Methods::getKey($this->method),
            'total_amount'  => $this->total_amount,
            'status'        => $this->status,
            'reference'     => $this->reference,
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
}
