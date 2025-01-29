<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeansOfIdentification;

class MeansOfIdentificationSeeder extends Seeder
{
    public function run()
    {
        $means = ['Passport', 'Driver License', 'National ID', 'Voter ID'];
        
        foreach ($means as $mean) {
            MeansOfIdentification::create(['name' => $mean]);
        }
    }
}
