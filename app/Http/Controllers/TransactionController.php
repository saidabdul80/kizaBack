<?php
namespace App\Http\Controllers;

use App\Enums\Methods;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\ExchangeRate;
use App\Models\SavedRecipient;
use App\Models\Transaction;
use App\Services\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Contracts\TransactionServiceInterface;


class TransactionController extends Controller
{

    protected $transactionService;
    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {

        $transactions = Transaction::filter([
            'customer'   => userId(),
        ])->with('customer', 'currencyFrom', 'currencyTo', 'recipient')->paginate(15);
        return TransactionResource::collection($transactions);
    }
    
    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        $rate = exchange()->rate($validated['currency_id_from'], $validated['currency_id_to']);

        if (!$rate) {
            return response()->json(['message' => 'Exchange rate not found'], 404);
        }

        $validated['rate'] = $rate;
        $validated['total_amount'] = ($validated['amount'] * $rate);// - $validated['fees'];
        $validated['customer_id']   = userId();
        $validated['fees'] = 0;
        $validated['type'] = 'send';
        $validated['method'] = Methods::getValue(strtoupper($request->method));

        try {
            $transaction = $this->transactionService->createTransaction($validated, $request->save_recipient);
            return response()->json(new TransactionResource($transaction), 200);
        } catch (\Exception $e) {
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
 
    public function handleTransaction(Request $request, Transaction $transaction, $type)
    {
        $request->validate([
            'type' => 'required|in:received,completed',
        ]);
        
        //action, data
        $this->transactionService->handleTransaction($type, $transaction);
        return response()->json(["message" => "Transaction updated successfully"]);
    }
  
}
