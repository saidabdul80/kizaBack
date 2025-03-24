<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'amount'            => 'required|numeric|min:0.01',
            'currency_id_from'  => 'required|exists:currencies,id',
            'currency_id_to'    => 'required|exists:currencies,id',
            'method'            => 'required|in:mobile_money,bank_deposit,cash_pick_up',
            'save_recipient'    => 'nullable|boolean',
            'recipient'         => 'required|array',


            'recipient.first_name'  => 'required_if:method,mobile_money,cash_pick_up|string|max:255',
            'recipient.last_name'   => 'required_if:method,mobile_money,cash_pick_up|string|max:255',
            'recipient.phone_number'=> 'required_if:method,mobile_money|nullable|string|max:15',
            'recipient.email'       => 'required_if:method,cash_pick_up|nullable|email|max:255',
            'recipient.bank_name'   => 'required_if:method,bank_deposit|nullable|string|max:255',
            'recipient.account_name'=> 'required_if:method,bank_deposit|nullable|string|max:255',
            'recipient.account_number' => 'required_if:method,bank_deposit|nullable|string|max:50',
        ];
    }
}
