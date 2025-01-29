<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            MeansOfIdentificationSeeder::class,
            AjoSeeder::class,
            AjoMemberSeeder::class,
          //  UserIdentificationSeeder::class,
            //WalletSeeder::class,
            //TransactionSeeder::class,
            //WalletTransactionSeeder::class,
        ]);
    }
}
