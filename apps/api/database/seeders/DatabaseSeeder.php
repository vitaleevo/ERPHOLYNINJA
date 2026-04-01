<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ClinicSeeder::class,
            SpecialtySeeder::class,
            InsuranceSeeder::class,
            AngolaLocationsSeeder::class,
            ICD10CodesSeeder::class,
            ClinicalTemplatesSeeder::class,
            MedicationSeeder::class,
            LabTestCategorySeeder::class,
            LabTestSeeder::class,
            UserSeeder::class,
        ]);
    }
}
