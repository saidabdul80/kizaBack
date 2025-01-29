<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserIdentification;
use App\Models\User;
use App\Models\MeansOfIdentification;

class UserIdentificationSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $meansOfIdentifications = MeansOfIdentification::all();

        $users->each(function ($user) use ($meansOfIdentifications) {
            UserIdentification::factory()->count(2)->create([
                'user_id' => $user->id,
                'type' => $meansOfIdentifications->random()->id,
            ]);
        });
    }
}
