<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        if (Clinic::count() > 0) {
            return;
        }

        $clinics = [
            [
                'name' => 'Clínica Sagrada Esperança',
                'email' => 'contato@sagradaesperanca.ao',
                'phone' => '+244 923 000 000',
                'nif' => '500123456',
                'address' => 'Rua Rainha Ginga, Luanda, Angola',
                'slug' => 'sagrada-esperanca',
                'plan' => 'professional',
                'status' => 'active',
            ],
            [
                'name' => 'Centro Médico Boa Vida',
                'email' => 'info@boavida.ao',
                'phone' => '+244 934 000 000',
                'nif' => '500234567',
                'address' => 'Avenida Independência, Benguela, Angola',
                'slug' => 'boa-vida',
                'plan' => 'basic',
                'status' => 'active',
            ],
            [
                'name' => 'Hospital Privado de Luanda',
                'email' => 'geral@hpl.ao',
                'phone' => '+244 912 000 000',
                'nif' => '500345678',
                'address' => 'Talatona, Luanda, Angola',
                'slug' => 'hospital-privado-luanda',
                'plan' => 'enterprise',
                'status' => 'active',
            ],
        ];

        foreach ($clinics as $clinic) {
            Clinic::create($clinic);
        }
    }
}
