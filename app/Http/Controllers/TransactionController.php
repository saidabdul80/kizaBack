<?php
namespace App\Http\Controllers;

use App\Enums\Methods;
use App\Http\Resources\TransactionResource;
use App\Models\ExchangeRate;
use App\Models\SavedRecipient;
use App\Models\Transaction;
use App\Services\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::filter([
            'customer'   => $request->user()->id,
        ])->with('customer', 'currencyFrom', 'currencyTo', 'recipient')->get();
        return TransactionResource::collection($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount'            => 'required|numeric|min:0.01',
            'currency_id_from'  => 'required|exists:currencies,id',
            'currency_id_to'    => 'required|exists:currencies,id',
            'method'            => 'required|in:mobile_money,bank_deposit,cash_pick_up',
            'save_recipient'    => 'nullable|boolean',
            'recipient'         => 'required|array',
    
            // Common fields
            'recipient.first_name'  => ['required_if:method,mobile_money,cash_pick_up', 'string', 'max:255'],
            'recipient.last_name'   => ['required_if:method,mobile_money,cash_pick_up', 'string', 'max:255'],
            'recipient.phone_number'=> ['required_if:method,mobile_money', 'nullable', 'string', 'max:15'],
            'recipient.email'       => ['required_if:method,cash_pick_up', 'nullable', 'email', 'max:255'],
            'recipient.bank_name'   => ['required_if:method,bank_deposit', 'nullable', 'string', 'max:255'],
            'recipient.account_name'=> ['required_if:method,bank_deposit', 'nullable', 'string', 'max:255'],
            'recipient.account_number' => ['required_if:method,bank_deposit', 'nullable', 'string', 'max:50'],
        ]);
    

        // Get exchange rate from backend
        $exchangeRate = ExchangeRate::where('currency_id_from', $validated['currency_id_from'])
            ->where('currency_id_to', $validated['currency_id_to'])
            ->first();

        if (!$exchangeRate) {
            return response()->json(['message' => 'Exchange rate not found'], 404);
        }

        // Calculate total amount using backend rate
        $validated['rate'] = $exchangeRate->rate;
        $validated['total_amount'] = ($validated['amount'] * $exchangeRate->rate);// - $validated['fees'];
        $validated['customer_id']   = $request->user()->id;
        $validated['fees'] = 0;
        $validated['type'] = 'send';
        $validated['method'] = Methods::getValue(strtoupper($request->method));
        DB::beginTransaction();
        try{
            if ($request->save_recipient) {
                $recipientData = ['customer_id' => $request->user()->id, 'method' => $validated['method']];

                // Apply validation fields based on the method
                if ($validated['method'] === Methods::MOBILE_MONEY) {
                    $recipientData += [
                        'first_name'   => $validated['recipient']['first_name'],
                        'last_name'    => $validated['recipient']['last_name'],
                        'phone_number' => $validated['recipient']['phone_number'],
                    ];
                } elseif ($validated['method'] === Methods::BANK_DEPOSIT) {
                    $recipientData += [
                        'bank_name'     => $validated['recipient']['bank_name'],
                        'account_name'  => $validated['recipient']['account_name'],
                        'account_number'=> $validated['recipient']['account_number'],
                    ];
                } elseif ($validated['method'] === Methods::CASH_PICK_UP) {
                    $recipientData += [
                        'first_name' => $validated['recipient']['first_name'],
                        'last_name'  => $validated['recipient']['last_name'],
                        'email'      => $validated['recipient']['email'] ?? null,
                        'phone_number' => $validated['recipient']['phone_number'],
                    ];
                }

                // Remove null values
                $recipientData = array_filter($recipientData, fn($value) => !is_null($value));

                // Define unique criteria based on method
                $conditions = [
                    'customer_id' => $request->user()->id,
                    'method'      => $validated['method'],
                ];
                
                if ($validated['method'] === Methods::MOBILE_MONEY || $validated['method'] === Methods::CASH_PICK_UP) {
                    $conditions['phone_number'] = $validated['recipient']['phone_number'];
                } elseif ($validated['method'] === 'bank_deposit') {
                    $conditions['account_number'] = $validated['recipient']['account_number'];
                }

                // Update or create recipient
                $recipient = SavedRecipient::updateOrCreate($conditions, $recipientData);

                $validated['recipient_id'] = $recipient->id;
                $validated['recipients'] = null; // Use saved recipient
            } else {
                // Store recipient details as JSON
                $validated['recipient_id'] = null;
                $validated['recipients'] = $validated['recipient'];
            }

            $validated['reference'] = Util::generateReferenceCode();
         
            // Create Transaction
            $transaction = Transaction::create($validated);
            DB::commit();
            return response()->json(new TransactionResource($transaction),200);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, $id)
    {
        $transaction = Transaction::filter([
            'customer'   => $id,
        ])->with('customer', 'currencyFrom', 'currencyTo', 'recipient')->findOrFail($id);
        return new TransactionResource($transaction);
    }
    

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:initiated,processing,completed,failed',
        ]);

        $transaction->update($request->only('status'));

        return response()->json(['message' => 'Transaction updated successfully']);
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
