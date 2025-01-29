<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\User;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $users->each(function ($user) {
            Transaction::factory()->count(5)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
