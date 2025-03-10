<?php
namespace App\Http\Controllers;

use App\Http\Resources\TransactionResource;
use App\Models\ExchangeRate;
use App\Models\SavedRecipient;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('customer', 'currencyFrom', 'currencyTo', 'recipient')->get();
        return TransactionResource::collection($transactions);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'amount'            => 'required|numeric|min:0.01',
            'currency_id_from'  => 'required|exists:currencies,id',
            'currency_id_to'    => 'required|exists:currencies,id',
            'type'              => 'required|in:send,receive',
            'fees'              => 'required|numeric|min:0',
            'processed_by'      => 'nullable|exists:users,id',
            'reference'         => 'required|string|unique:transactions,reference',
            'save_recipient'    => 'nullable|boolean',
            'recipient'         => 'required|array',
            'recipient.first_name'  => 'required|string|max:255',
            'recipient.last_name'   => 'nullable|string|max:255',
            'recipient.phone_number'=> 'required|string|max:15',
            'recipient.email'       => 'nullable|email|max:255',
            'recipient.bank_name'   => 'required|string|max:255',
            'recipient.account_name'=> 'required|string|max:255',
            'recipient.account_number' => 'required|string|max:50',
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
        $validated['total_amount'] = ($validated['amount'] * $exchangeRate->rate) - $validated['fees'];

        if ($request->save_recipient) {
            // Save recipient and set recipient_id
            $recipient = SavedRecipient::updateOrCreate(
                [
                    'customer_id'   => $validated['customer_id'],
                    'first_name'   => $validated['first_name'],
                    'last_name'   => $validated['last_name'],
                    'phone_number'  => $validated['recipient']['phone_number'],
                    'account_number'=> $validated['recipient']['account_number'],
                ],
                $validated['recipient']
            );

            $validated['recipient_id'] = $recipient->id;
            $validated['recipients'] = null; // Use saved recipient
        } else {
            // Store recipient details as JSON
            $validated['recipient_id'] = null;
            $validated['recipients'] = $validated['recipient'];
        }

        // Create Transaction
        $transaction = Transaction::create($validated);

        return response()->json($transaction,200);
    }


    public function show($id)
    {
        $transaction = Transaction::with('customer', 'currencyFrom', 'currencyTo', 'recipient')->findOrFail($id);
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
