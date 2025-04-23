<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
          //ExchangeRateSeeder::class,
        UserSeeder::class,
            //WalletSeeder::class,
            //TransactionSeeder::class,
            //WalletTransactionSeeder::class,
        ]);
    }
}
