<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->name,
            'last_name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_number' => $this->faker->phoneNumber,
            'is_verified_email' => $this->faker->boolean,
            'is_verified_phone_number' => $this->faker->boolean,
            'password' => Hash::make('password'), // password
            'two_factor_enabled' => $this->faker->boolean,
            'two_factor_secret' => $this->faker->optional()->sha256,
            'picture_url' => $this->faker->optional()->imageUrl,
            'two_factor_recovery_codes' => '$this->faker->optional()->json'
        ];
    }
}
