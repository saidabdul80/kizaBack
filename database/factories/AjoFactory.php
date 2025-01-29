<?php

namespace Database\Factories;

use App\Models\Ajo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AjoFactory extends Factory
{
    protected $model = Ajo::class;

    public function definition()
    {
        $startDate = Carbon::parse($this->faker->date);
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'frequency' => $this->faker->randomElement([1, 2]),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $startDate->copy()->addDays($this->faker->numberBetween(60, 120))->format('Y-m-d'),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }   
}
