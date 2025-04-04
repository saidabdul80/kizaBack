<?php
namespace App\Http\Resources;

use App\Enums\Methods;
use App\Services\Util;
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
            'recipient'     => $this->formatRecipient($this->recipient_details),// Handles both saved and unsaved recipients
            'type'          => $this->type,
            'rate'          => $this->rate,
            'fees'          => $this->fees,
            'method'        => Methods::getKey($this->method),
            'total_amount'  => $this->total_amount,
            'status'        => $this->status,
            'reference'     => $this->reference,
            'receipts'      => $this->getReceipts($this->receipts), 
            'created_at'    => $this->created_at->toDateTimeString(),
        ];
    }
    
    protected function getReceipts($receipts){

        if(!$receipts){
            return [];
        }

        foreach ($receipts as $key => &$receipt) {
            $receipts[$key] =  Util::publicUrl($receipt);
        }

        return $receipts;
    }
     /**
     * Format recipient details to match RecipientResource structure.
     *
     * @param mixed $recipient
     * @return array|null
     */
    protected function formatRecipient($recipient): ?array
    {
        if (!$recipient) {
            return null;
        }

        if (is_array($recipient)) {
            return [
                'id'            => $recipient['id'] ?? null,
                'name'          => (isset($recipient['method']) && ($recipient['method'] === Methods::MOBILE_MONEY || $recipient['method'] === Methods::CASH_PICK_UP))
                    ? ($recipient['first_name'] . ' ' . $recipient['last_name'])
                    : ($recipient['account_name'] ?? ''),
                'method' => Methods::getKey($this->method),
                'customer_id'   => $recipient['customer_id'] ?? null,
                'first_name'    => $recipient['first_name'] ?? null,
                'last_name'     => $recipient['last_name'] ?? null,
                'phone_number'  => $recipient['phone_number'] ?? null,
                'email'         => $recipient['email'] ?? null,
                'bank_name'     => $recipient['bank_name'] ?? null,
                'account_name'  => $recipient['account_name'] ?? null,
                'account_number'=> $recipient['account_number'] ?? null,
                'created_at'    => $recipient['created_at'] ?? null,
                'updated_at'    => $recipient['updated_at'] ?? null,
            ];
        }

        // If it's an Eloquent model, use RecipientResource to format it
        return (new RecipientResource($recipient))->toArray(request());
    }
}
