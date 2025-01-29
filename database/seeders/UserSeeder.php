<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(10)->create();
        User::first()->update(['email'=> 'ajo-user@gmail.com']);
    }
}
