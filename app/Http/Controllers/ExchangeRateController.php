<?php
namespace App\Http\Controllers;

use App\Http\Resources\ExchangeRateToResource;
use App\Models\Currency;
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


    public function getRatesByCurrency($currencyCode)
    {
        // Find the currency ID
        $currency = Currency::where('code', strtoupper($currencyCode))->first();

        if (!$currency) {
            return response()->json(['message' => 'Currency not found'], 404);
        }

        // Get exchange rates where the given currency is the base (from)
        $exchangeRates = ExchangeRate::with(['currencyTo'])
            ->where('currency_id_from', $currency->id)
            ->where('is_active', true)
            ->get();

        if ($exchangeRates->isEmpty()) {
            return response()->json(['message' => 'No exchange rates found for this currency'], 404);
        }

        return response()->json([
            'base_currency' => [
                'code' => $currency->code,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'flag' => $currency->flag
            ],
            'exchange_rates' => ExchangeRateToResource::collection($exchangeRates)
        ]);
    }
}
