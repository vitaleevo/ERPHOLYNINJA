<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\MedicationBatch;
use App\Models\Clinic;
use Illuminate\Database\Seeder;

class MedicationSeeder extends Seeder
{
    public function run(): void
    {
        if (Medication::count() > 0) {
            return;
        }

        $medications = [
            // Analgésicos e Antitérmicos
            [
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Paracetamol',
                'brand' => 'Genérico',
                'dosage' => '500mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => false,
                'reference_price' => 500.00,
                'indications' => 'Dor e febre',
            ],
            [
                'name' => 'Ibuprofeno 400mg',
                'generic_name' => 'Ibuprofeno',
                'brand' => 'Advil',
                'dosage' => '400mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => false,
                'reference_price' => 800.00,
                'indications' => 'Dor, inflamação e febre',
            ],
            [
                'name' => 'Aspirina 100mg',
                'generic_name' => 'Ácido Acetilsalicílico',
                'brand' => 'Bayer',
                'dosage' => '100mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => false,
                'reference_price' => 600.00,
                'indications' => 'Prevenção cardiovascular',
            ],
            
            // Antibióticos (exigem receita)
            [
                'name' => 'Amoxicilina 500mg',
                'generic_name' => 'Amoxicilina',
                'brand' => 'Clamoxyl',
                'dosage' => '500mg',
                'form' => 'Cápsula',
                'route' => 'Oral',
                'requires_prescription' => true,
                'requires_special_control' => true,
                'reference_price' => 1500.00,
                'indications' => 'Infecções bacterianas',
            ],
            [
                'name' => 'Azitromicina 500mg',
                'generic_name' => 'Azitromicina',
                'brand' => 'Zithromax',
                'dosage' => '500mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => true,
                'requires_special_control' => true,
                'reference_price' => 2500.00,
                'indications' => 'Infecções respiratórias',
            ],
            
            // Anti-hipertensivos
            [
                'name' => 'Losartana 50mg',
                'generic_name' => 'Losartana',
                'brand' => 'Cozaar',
                'dosage' => '50mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => true,
                'reference_price' => 1800.00,
                'indications' => 'Hipertensão arterial',
            ],
            [
                'name' => 'Enalapril 10mg',
                'generic_name' => 'Enalapril',
                'brand' => 'Renitec',
                'dosage' => '10mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => true,
                'reference_price' => 1200.00,
                'indications' => 'Hipertensão e insuficiência cardíaca',
            ],
            
            // Antidiabéticos
            [
                'name' => 'Metformina 850mg',
                'generic_name' => 'Metformina',
                'brand' => 'Glifage',
                'dosage' => '850mg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => true,
                'reference_price' => 900.00,
                'indications' => 'Diabetes tipo 2',
            ],
            
            // Gastrointestinais
            [
                'name' => 'Omeprazol 20mg',
                'generic_name' => 'Omeprazol',
                'brand' => 'Losec',
                'dosage' => '20mg',
                'form' => 'Cápsula',
                'route' => 'Oral',
                'requires_prescription' => false,
                'reference_price' => 700.00,
                'indications' => 'Refluxo e úlcera gástrica',
            ],
            
            // Vitaminas
            [
                'name' => 'Vitamina C 1g',
                'generic_name' => 'Ácido Ascórbico',
                'brand' => 'Redoxon',
                'dosage' => '1g',
                'form' => 'Comprimido Efervescente',
                'route' => 'Oral',
                'requires_prescription' => false,
                'reference_price' => 400.00,
                'indications' => 'Suplementação de vitamina C',
            ],
            [
                'name' => 'Complexo B',
                'generic_name' => 'Vitaminas do Complexo B',
                'brand' => 'Neurobion',
                'dosage' => '100mg/100mg/1000mcg',
                'form' => 'Comprimido',
                'route' => 'Oral',
                'requires_prescription' => false,
                'reference_price' => 600.00,
                'indications' => 'Deficiência de vitaminas B',
            ],
        ];

        Clinic::all()->each(function ($clinic) use ($medications) {
            foreach ($medications as $medData) {
                $medication = Medication::create([
                    ...$medData,
                    'clinic_id' => $clinic->id,
                    'manufacturer' => 'Laboratório Farmacêutico',
                    'registration_number' => 'MED-' . rand(10000, 99999),
                    'is_active' => true,
                ]);

                // Criar 2-3 lotes para cada medicamento
                $batchesCount = rand(2, 3);
                for ($i = 0; $i < $batchesCount; $i++) {
                    $expiryDate = now()->addDays(rand(30, 730)); // 1 mês a 2 anos
                    $someExpired = rand(1, 10) <= 2; // 20% de chance de estar vencido
                    
                    if ($someExpired) {
                        $expiryDate = now()->subDays(rand(1, 90));
                    }

                    MedicationBatch::create([
                        'medication_id' => $medication->id,
                        'clinic_id' => $clinic->id,
                        'batch_number' => 'LOT-' . strtoupper(substr(md5(time() . rand()), 0, 8)),
                        'manufacturing_date' => now()->subMonths(rand(1, 12)),
                        'expiry_date' => $expiryDate,
                        'initial_quantity' => rand(50, 200),
                        'current_quantity' => rand(10, 100),
                        'cost_price' => $medData['reference_price'] * 0.6,
                        'sale_price' => $medData['reference_price'],
                        'storage_location' => 'Prateleira ' . chr(rand(65, 70)) . '-' . rand(1, 20),
                        'status' => $someExpired ? 'expired' : 'active',
                    ]);
                }
            }
        });
    }
}
