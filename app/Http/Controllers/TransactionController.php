<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        return Transaction::with(['customer', 'currency', 'admin'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric',
            'currency_id' => 'required|exists:currencies,id',
            'type' => 'required|in:deposit,fulfill',
            'rate' => 'required|numeric',
            'fees' => 'required|numeric',
            'processed_by' => 'nullable|exists:users,id',
            'reference' => 'required|unique:transactions,reference',
        ]);

        return Transaction::create($request->all());
    }

    public function show($id)
    {
        return Transaction::with(['customer', 'currency', 'admin'])->findOrFail($id);
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
