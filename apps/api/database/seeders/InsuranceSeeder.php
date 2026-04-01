<?php

namespace Database\Seeders;

use App\Models\Insurance;
use App\Models\Clinic;
use Illuminate\Database\Seeder;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        if (Insurance::count() > 0) {
            return;
        }

        $insurances = [
            ['name' => 'Seguro Nacional de Saúde', 'code' => 'SNS'],
            ['name' => 'Jornada de Angola', 'code' => 'JA'],
            ['name' => 'Ensa - Seguros de Angola', 'code' => 'ENSA'],
            ['name' => 'Sanlam Maianga Vida', 'code' => 'SANLAM'],
            ['name' => 'Universal Seguros', 'code' => 'UNIVERSAL'],
            ['name' => 'Fidelidade Angola', 'code' => 'FIDELIDADE'],
            ['name' => 'Particular', 'code' => 'PART'],
        ];

        Clinic::all()->each(function ($clinic) use ($insurances) {
            foreach ($insurances as $insurance) {
                Insurance::create([
                    'clinic_id' => $clinic->id,
                    'name' => $insurance['name'],
                    'code' => $insurance['code'],
                    'description' => 'Seguro de saúde',
                    'is_active' => true,
                ]);
            }
        });
    }
}
