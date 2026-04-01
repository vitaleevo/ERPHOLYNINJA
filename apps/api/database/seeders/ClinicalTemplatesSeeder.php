<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClinicalTemplate;
use App\Models\Clinic;

class ClinicalTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        if (ClinicalTemplate::count() > 0) {
            return;
        }

        $clinicId = Clinic::first()?->id;
        $userId = \App\Models\User::where('role', 'admin')->first()?->id;

        if (!$userId) {
            return;
        }

        $templates = [
            [
                'name' => 'Consulta de Clínica Geral',
                'specialty' => 'Medicina Geral',
                'type' => 'consultation',
                'structure' => json_encode(['subjective' => 'Queixa principal', 'objective' => 'Exame físico', 'assessment' => 'Diagnóstico', 'plan' => 'Tratamento']),
                'common_diagnoses' => json_encode(['J00', 'J06', 'R50']),
                'is_global' => true,
                'clinic_id' => $clinicId,
            ],
            [
                'name' => 'Consulta de Pediatria',
                'specialty' => 'Pediatria',
                'type' => 'consultation',
                'structure' => json_encode(['subjective' => 'História pediátrica', 'objective' => 'Peso, altura', 'assessment' => 'Avaliação', 'plan' => 'Vacinação']),
                'common_diagnoses' => json_encode(['J00', 'B50']),
                'is_global' => true,
                'clinic_id' => $clinicId,
            ],
            [
                'name' => 'Consulta de Ginecologia',
                'specialty' => 'Ginecologia',
                'type' => 'examination',
                'structure' => json_encode(['subjective' => 'História menstrual', 'objective' => 'Exame especular', 'assessment' => 'Citopatológico', 'plan' => 'Prevenção']),
                'common_diagnoses' => json_encode(['N39']),
                'is_global' => true,
                'clinic_id' => $clinicId,
            ],
        ];

        foreach ($templates as $template) {
            $template['created_by'] = $clinicId;
            ClinicalTemplate::create($template);
        }
    }
}