<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Campos de identificação angolana
            $table->enum('document_type', ['bi', 'nif', 'passport', 'cedao'])->nullable()->after('email');
            $table->string('document_number')->nullable()->after('document_type');
            
            // Localização Angola
            $table->string('province')->nullable()->after('address');
            $table->string('municipality')->nullable()->after('province');
            $table->string('district')->nullable()->after('municipality');
            
            // Dados clínicos adicionais
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable()->after('gender');
            $table->string('rh_factor')->nullable()->after('blood_type');
            
            // Campos específicos para validação
            $table->boolean('is_insured')->default(false)->after('insurance_number');
            $table->string('patient_card_number')->nullable()->after('is_insured');
            
            // Índices para pesquisa e duplicados
            $table->index(['document_type', 'document_number']);
            $table->index(['phone', 'email']);
            $table->index(['province', 'municipality']);
        });

        // Migrar dados existentes
        DB::statement('UPDATE patients SET document_type = "bi" WHERE bi_number IS NOT NULL');
        DB::statement('UPDATE patients SET document_number = bi_number WHERE bi_number IS NOT NULL');
        DB::statement('UPDATE patients SET document_type = "nif" WHERE nif IS NOT NULL AND document_type IS NULL');
        DB::statement('UPDATE patients SET document_number = nif WHERE nif IS NOT NULL AND document_number IS NULL');
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['document_type', 'document_number']);
            $table->dropIndex(['phone', 'email']);
            $table->dropIndex(['province', 'municipality']);
            
            $table->dropColumn([
                'document_type',
                'document_number',
                'province',
                'municipality',
                'district',
                'blood_type',
                'rh_factor',
                'is_insured',
                'patient_card_number'
            ]);
        });
    }
};
