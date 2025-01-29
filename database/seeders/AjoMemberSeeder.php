<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ajo;
use App\Models\AjoMember;
use App\Models\User;

class AjoMemberSeeder extends Seeder
{
    public function run()
    {
        $ajos = Ajo::all();
        $users = User::all();

        $ajos->each(function ($ajo) use ($users) {
            $ajoMembers = $users->random(5);
            $ajoMembers->each(function ($user) use ($ajo) {
                AjoMember::factory()->create([
                    'ajo_id' => $ajo->id,
                    'user_id' => $user->id,
                ]);
            });
        });
    }
}
