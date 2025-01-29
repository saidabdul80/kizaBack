<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Wallet;
use App\Models\User;

class WalletSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $users->each(function ($user) {
            Wallet::factory()->create([
                'owner_id' => $user->id,
                'owner_type' => User::class,
            ]);
        });
    }
}
