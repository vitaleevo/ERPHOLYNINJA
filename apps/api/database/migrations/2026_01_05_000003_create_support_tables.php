<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de códigos CID-10/ICD-10
        Schema::create('icd10_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('description');
            $table->text('full_description')->nullable();
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('synonyms')->nullable();
            $table->timestamps();
            
            $table->index('code');
            $table->index('category');
        });

        // Tabela de províncias de Angola
        Schema::create('angola_provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->string('capital')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 12, 8)->nullable();
            $table->timestamps();
        });

        // Tabela de municípios
        Schema::create('angola_municipalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('angola_provinces')->onDelete('cascade');
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->timestamps();
            
            $table->index(['province_id', 'name']);
        });

        // Tabela de templates clínicos
        Schema::create('clinical_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('specialty')->nullable();
            $table->enum('type', ['consultation', 'procedure', 'examination'])->default('consultation');
            $table->json('structure')->comment('Estrutura SOAP predefinida');
            $table->json('common_diagnoses')->nullable()->comment('Códigos CID-10 comuns');
            $table->json('default_medications')->nullable();
            $table->boolean('is_global')->default(false)->comment('Template global do sistema');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            
            $table->index(['clinic_id', 'type']);
            $table->index('is_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_templates');
        Schema::dropIfExists('angola_municipalities');
        Schema::dropIfExists('angola_provinces');
        Schema::dropIfExists('icd10_codes');
    }
};
