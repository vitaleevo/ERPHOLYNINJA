<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Specialty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $specialties = Specialty::all();

        Clinic::all()->each(function ($clinic) use ($specialties) {
            User::create([
                'clinic_id' => $clinic->id,
                'name' => 'Administrador ' . $clinic->name,
                'email' => 'admin@' . $clinic->slug . '.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+244 923 000 001',
                'is_active' => true,
            ]);

            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'clinic_id' => $clinic->id,
                    'name' => 'Dr. Médico ' . $i,
                    'email' => 'medico' . $i . '@' . $clinic->slug . '.com',
                    'password' => Hash::make('password'),
                    'role' => 'doctor',
                    'specialty_id' => $specialties->random()->id,
                    'phone' => '+244 923 ' . rand(100000, 999999),
                    'is_active' => true,
                ]);
            }

            for ($i = 1; $i <= 3; $i++) {
                User::create([
                    'clinic_id' => $clinic->id,
                    'name' => 'Recepcionista ' . $i,
                    'email' => 'recep' . $i . '@' . $clinic->slug . '.com',
                    'password' => Hash::make('password'),
                    'role' => 'receptionist',
                    'phone' => '+244 924 ' . rand(100000, 999999),
                    'is_active' => true,
                ]);
            }

            if (in_array($clinic->plan, ['professional', 'enterprise'])) {
                for ($i = 1; $i <= 2; $i++) {
                    User::create([
                        'clinic_id' => $clinic->id,
                        'name' => 'Farmacêutico ' . $i,
                        'email' => 'farmacia' . $i . '@' . $clinic->slug . '.com',
                        'password' => Hash::make('password'),
                        'role' => 'pharmacist',
                        'phone' => '+244 925 ' . rand(100000, 999999),
                        'is_active' => true,
                    ]);
                }
            }

            if ($clinic->plan === 'enterprise') {
                User::create([
                    'clinic_id' => $clinic->id,
                    'name' => 'Contabilidade',
                    'email' => 'contabilidade@' . $clinic->slug . '.com',
                    'password' => Hash::make('password'),
                    'role' => 'accountant',
                    'phone' => '+244 926 ' . rand(100000, 999999),
                    'is_active' => true,
                ]);
            }
        });

        $firstClinic = Clinic::first();
        if ($firstClinic) {
            User::create([
                'clinic_id' => $firstClinic->id,
                'name' => 'Admin Principal',
                'email' => 'admin@medangola.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+244 999 000 000',
                'is_active' => true,
            ]);
        }
    }
}