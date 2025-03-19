<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExchangeRate;
use App\Models\Currency;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Currencies
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'flag' => 'https://flagcdn.com/us.svg'],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'flag' => 'https://flagcdn.com/ng.svg'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'flag' => 'https://flagcdn.com/eu.svg'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'flag' => 'https://flagcdn.com/ca.svg'],
            ['code' => 'BIF', 'name' => 'Burundian Franc', 'symbol' => 'FBu', 'flag' => 'https://flagcdn.com/bi.svg'],
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'flag' => 'https://flagcdn.com/ug.svg'],
            ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'FRw', 'flag' => 'https://flagcdn.com/rw.svg'],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                ['name' => $currency['name'], 'symbol' => $currency['symbol'], 'flag' => $currency['flag']]
            );
        }

        // Fetch currency IDs
        $currencyIds = Currency::pluck('id', 'code')->toArray();

        // Seed Exchange Rates
        $exchangeRates = [
            ['currency_id_from' => $currencyIds['USD'], 'currency_id_to' => $currencyIds['NGN'], 'rate' => 1300.50],
            ['currency_id_from' => $currencyIds['NGN'], 'currency_id_to' => $currencyIds['USD'], 'rate' => 0.00077],
            ['currency_id_from' => $currencyIds['EUR'], 'currency_id_to' => $currencyIds['NGN'], 'rate' => 1400.75],
            ['currency_id_from' => $currencyIds['NGN'], 'currency_id_to' => $currencyIds['EUR'], 'rate' => 0.00071],
            ['currency_id_from' => $currencyIds['CAD'], 'currency_id_to' => $currencyIds['NGN'], 'rate' => 950.30],
            ['currency_id_from' => $currencyIds['NGN'], 'currency_id_to' => $currencyIds['CAD'], 'rate' => 0.00105],
            ['currency_id_from' => $currencyIds['CAD'], 'currency_id_to' => $currencyIds['BIF'], 'rate' => 2070.00],
            ['currency_id_from' => $currencyIds['CAD'], 'currency_id_to' => $currencyIds['UGX'], 'rate' => 2800.00],
            ['currency_id_from' => $currencyIds['CAD'], 'currency_id_to' => $currencyIds['RWF'], 'rate' => 900.50],
        ];

        foreach ($exchangeRates as $rate) {
            ExchangeRate::updateOrCreate(
                [
                    'currency_id_from' => $rate['currency_id_from'],
                    'currency_id_to'   => $rate['currency_id_to'],
                ],
                ['rate' => $rate['rate'], 'is_active' => true]
            );
        }
    }
}
