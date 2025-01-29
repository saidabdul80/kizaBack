<?php

namespace Database\Factories;

use App\Models\AjoMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AjoMemberFactory extends Factory
{
    protected $model = AjoMember::class;

    public function definition()
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
