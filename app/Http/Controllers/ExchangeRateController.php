<?php
namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function index()
    {
        return ExchangeRate::with(['currencyFrom', 'currencyTo'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency_id_from' => 'required|exists:currencies,id',
            'currency_id_to' => 'required|exists:currencies,id',
            'rate' => 'required|numeric',
        ]);

        return ExchangeRate::create($request->all());
    }

    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        $request->validate(['rate' => 'required|numeric']);
        $exchangeRate->update(['rate' => $request->rate]);
        return response()->json(['message' => 'Exchange rate updated successfully']);
    }

    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();
        return response()->json(['message' => 'Exchange rate deleted successfully']);
    }
}
