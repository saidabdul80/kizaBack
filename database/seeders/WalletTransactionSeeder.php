<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WalletTransaction;
use App\Models\Wallet;

class WalletTransactionSeeder extends Seeder
{
    public function run()
    {
        $wallets = Wallet::all();

        $wallets->each(function ($wallet) {
            WalletTransaction::factory()->count(10)->create([
                'wallet_id' => $wallet->id,
            ]);
        });
    }
}
