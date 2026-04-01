<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Icd10Code;

class ICD10CodesSeeder extends Seeder
{
    public function run(): void
    {
        if (Icd10Code::count() > 0) {
            return;
        }

        // Principais códigos CID-10 para uso comum
        $codes = [
            ['code' => 'A00', 'description' => 'Cólera', 'category' => 'Doenças infecciosas'],
            ['code' => 'A01', 'description' => 'Febres tifóide e paratifóide', 'category' => 'Doenças infecciosas'],
            ['code' => 'B50', 'description' => 'Malária por Plasmodium falciparum', 'category' => 'Doenças parasitárias'],
            ['code' => 'J00', 'description' => 'Nasofaringite aguda (resfriado comum)', 'category' => 'Doenças respiratórias'],
            ['code' => 'J06', 'description' => 'Infecção aguda das vias aéreas superiores', 'category' => 'Doenças respiratórias'],
            ['code' => 'J18', 'description' => 'Pneumonia por microrganismo não especificado', 'category' => 'Doenças respiratórias'],
            ['code' => 'E11', 'description' => 'Diabetes mellitus tipo 2', 'category' => 'Doenças endócrinas'],
            ['code' => 'E10', 'description' => 'Diabetes mellitus tipo 1', 'category' => 'Doenças endócrinas'],
            ['code' => 'I10', 'description' => 'Hipertensão essencial (primária)', 'category' => 'Doenças circulatórias'],
            ['code' => 'I11', 'description' => 'Doença cardíaca hipertensiva', 'category' => 'Doenças circulatórias'],
            ['code' => 'K29', 'description' => 'Gastrite e duodenite', 'category' => 'Doenças digestivas'],
            ['code' => 'K35', 'description' => 'Apendicite aguda', 'category' => 'Doenças digestivas'],
            ['code' => 'N39', 'description' => 'Outros transtornos do aparelho urinário', 'category' => 'Doenças geniturinárias'],
            ['code' => 'F41', 'description' => 'Outros transtornos ansiosos', 'category' => 'Transtornos mentais'],
            ['code' => 'F32', 'description' => 'Episódio depressivo', 'category' => 'Transtornos mentais'],
            ['code' => 'R50', 'description' => 'Febre de origem desconhecida', 'category' => 'Sintomas gerais'],
            ['code' => 'R51', 'description' => 'Cefaleia (dor de cabeça)', 'category' => 'Sintomas gerais'],
            ['code' => 'R10', 'description' => 'Dor abdominal e pélvica', 'category' => 'Sintomas gerais'],
            ['code' => 'S00', 'description' => 'Traumatismo superficial da cabeça', 'category' => 'Lesões'],
            ['code' => 'O80', 'description' => 'Parto único espontâneo', 'category' => 'Gravidez e parto'],
        ];

        foreach ($codes as $code) {
            Icd10Code::create($code);
        }
    }
}
