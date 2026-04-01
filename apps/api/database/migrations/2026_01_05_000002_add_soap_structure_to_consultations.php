<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Estrutura SOAP completa
            $table->text('subjective')->nullable()->after('chief_complaint');
            $table->text('objective')->nullable()->after('subjective');
            $table->text('assessment')->nullable()->after('objective');
            $table->text('plan')->nullable()->after('assessment');
            
            // Codificação CID-10/ICD-11
            $table->json('icd10_codes')->nullable()->after('diagnosis');
            $table->string('primary_diagnosis_code')->nullable()->after('icd10_codes');
            
            // Templates
            $table->foreignId('template_id')->nullable()->constrained('clinical_templates')->nullOnDelete();
            
            // Assinatura digital
            $table->boolean('is_signed')->default(false)->after('status');
            $table->datetime('signed_at')->nullable()->after('is_signed');
            $table->string('digital_signature_hash')->nullable()->after('signed_at');
            
            // Índices
            $table->index('primary_diagnosis_code');
        });

        // Migrar dados existentes
        DB::statement("UPDATE consultations SET subjective = chief_complaint WHERE chief_complaint IS NOT NULL");
        DB::statement("UPDATE consultations SET objective = symptoms WHERE symptoms IS NOT NULL");
        DB::statement("UPDATE consultations SET assessment = diagnosis WHERE diagnosis IS NOT NULL");
        DB::statement("UPDATE consultations SET plan = observations WHERE observations IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropIndex('primary_diagnosis_code');
            
            $table->dropColumn([
                'subjective',
                'objective',
                'assessment',
                'plan',
                'icd10_codes',
                'primary_diagnosis_code',
                'template_id',
                'is_signed',
                'signed_at',
                'digital_signature_hash'
            ]);
        });
    }
};
