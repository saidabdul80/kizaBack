<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ajo;
use App\Models\User;

class AjoSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $users->each(function ($user) {
            Ajo::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
