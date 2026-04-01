<?php

namespace Database\Seeders;

use App\Models\Specialty;
use App\Models\Clinic;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        if (Specialty::count() > 0) {
            return;
        }

        $specialties = [
            ['name' => 'Clínica Geral', 'description' => 'Médico de clínica geral'],
            ['name' => 'Cardiologia', 'description' => 'Especialista em doenças do coração'],
            ['name' => 'Dermatologia', 'description' => 'Especialista em doenças da pele'],
            ['name' => 'Endocrinologia', 'description' => 'Especialista em distúrbios hormonais'],
            ['name' => 'Gastroenterologia', 'description' => 'Especialista em doenças do aparelho digestivo'],
            ['name' => 'Ginecologia', 'description' => 'Especialista em saúde feminina'],
            ['name' => 'Neurologia', 'description' => 'Especialista em doenças do sistema nervoso'],
            ['name' => 'Oftalmologia', 'description' => 'Especialista em doenças dos olhos'],
            ['name' => 'Ortopedia', 'description' => 'Especialista em doenças do sistema musculoesquelético'],
            ['name' => 'Pediatria', 'description' => 'Especialista em saúde infantil'],
            ['name' => 'Psiquiatria', 'description' => 'Especialista em saúde mental'],
            ['name' => 'Reumatologia', 'description' => 'Especialista em doenças reumáticas'],
            ['name' => 'Urologia', 'description' => 'Especialista em doenças do sistema urinário'],
            ['name' => 'Otorrinolaringologia', 'description' => 'Especialista em doenças de ouvido, nariz e garganta'],
            ['name' => 'Anestesiologia', 'description' => 'Especialista em anestesia'],
        ];

        Clinic::all()->each(function ($clinic) use ($specialties) {
            foreach ($specialties as $specialty) {
                Specialty::create([
                    'clinic_id' => $clinic->id,
                    'name' => $specialty['name'],
                    'description' => $specialty['description'],
                    'is_active' => true,
                ]);
            }
        });
    }
}
