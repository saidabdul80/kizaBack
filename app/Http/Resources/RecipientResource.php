<?php

namespace App\Http\Resources;

use App\Enums\Methods;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fullName = $this->first_name . ' ' . $this->last_name;
        return [
            'id' => $this->id,
            'name' => $this->method === Methods::MOBILE_MONEY || Methods::CASH_PICK_UP ? $fullName :$this->account_name, 
            'customer_id' => $this->customer_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
