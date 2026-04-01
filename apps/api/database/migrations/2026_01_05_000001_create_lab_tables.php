<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela de categorias de exames laboratoriais
        Schema::create('lab_test_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#3B82F6'); // Cor para identificação visual
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            
            $table->index(['clinic_id', 'is_active']);
            $table->index('code');
        });

        // Tabela de exames laboratoriais (testes)
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->uuid('category_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('unit_of_measurement')->nullable(); // mg/dL, g/L, etc.
            $table->decimal('min_reference_value', 10, 2)->nullable(); // Valor mínimo de referência
            $table->decimal('max_reference_value', 10, 2)->nullable(); // Valor máximo de referência
            $table->decimal('panic_min_value', 10, 2)->nullable(); // Valor crítico mínimo
            $table->decimal('panic_max_value', 10, 2)->nullable(); // Valor crítico máximo
            $table->string('sample_type')->default('blood'); // blood, urine, stool, etc.
            $table->integer('turnaround_time_hours')->default(24); // Tempo estimado em horas
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('requires_equipment')->default(false);
            $table->uuid('equipment_id')->nullable();
            $table->text('preparation_instructions')->nullable(); // Instruções de preparação
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('lab_test_categories')->onDelete('restrict');
            
            $table->index(['clinic_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index('code');
        });

        // Tabela de perfis de exames (grupos de exames)
        Schema::create('lab_test_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->boolean('is_discountable')->default(true);
            $table->integer('discount_percentage')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            
            $table->index(['clinic_id', 'is_active']);
        });

        // Tabela pivô para exames em perfis
        Schema::create('lab_profile_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('profile_id');
            $table->uuid('test_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on('lab_test_profiles')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('lab_tests')->onDelete('cascade');
            
            $table->unique(['profile_id', 'test_id']);
        });

        // Tabela de pedidos de exame laboratorial
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->uuid('consultation_id')->nullable();
            $table->uuid('patient_id');
            $table->uuid('doctor_id'); // Médico que solicitou
            $table->uuid('technician_id')->nullable(); // Técnico que processou
            $table->uuid('validator_id')->nullable(); // Diretor técnico que validou
            $table->string('accession_number')->unique(); // Número único do pedido
            $table->string('barcode')->unique()->nullable(); // Código de barras da amostra
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->enum('status', [
                'pending_collection',    // Aguardando coleta
                'collected',             // Coletado
                'in_progress',           // Em análise
                'pending_validation',    // Aguardando validação
                'validated',             // Validado
                'rejected',              // Rejeitado
                'cancelled'              // Cancelado
            ])->default('pending_collection');
            $table->string('rejection_reason')->nullable();
            $table->datetime('collection_datetime')->nullable(); // Data/hora da coleta
            $table->datetime('received_at')->nullable(); // Recebido no laboratório
            $table->datetime('started_at')->nullable(); // Iniciada a análise
            $table->datetime('completed_at')->nullable(); // Análise concluída
            $table->datetime('validated_at')->nullable(); // Validado pelo diretor
            $table->datetime('expected_delivery_at')->nullable(); // Previsão de entrega
            $table->text('clinical_notes')->nullable(); // Notas clínicas relevantes
            $table->text('observations')->nullable();
            $table->boolean('print_label')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('consultation_id')->references('id')->on('consultations')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('validator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['clinic_id', 'status']);
            $table->index(['patient_id', 'status']);
            $table->index('accession_number');
            $table->index('barcode');
        });

        // Tabela de itens do pedido de exame
        Schema::create('lab_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->uuid('test_id');
            $table->uuid('profile_id')->nullable();
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
                'rejected',
                'cancelled'
            ])->default('pending');
            $table->text('technician_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('request_id')->references('id')->on('lab_requests')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('lab_tests')->onDelete('restrict');
            $table->foreign('profile_id')->references('id')->on('lab_test_profiles')->onDelete('set null');
            
            $table->index(['request_id', 'status']);
        });

        // Tabela de resultados de exames laboratoriais
        Schema::create('lab_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('request_item_id');
            $table->uuid('test_id');
            $table->string('result_value')->nullable(); // Valor numérico ou textual
            $table->decimal('numeric_value', 15, 4)->nullable(); // Valor numérico para cálculos
            $table->string('text_result')->nullable(); // Resultado textual (para exames qualitativos)
            $table->string('unit')->nullable(); // Unidade de medida
            $table->decimal('reference_min', 10, 2)->nullable(); // Mínimo de referência
            $table->decimal('reference_max', 10, 2)->nullable(); // Máximo de referência
            $table->boolean('is_abnormal')->default(false);
            $table->boolean('is_critical')->default(false);
            $table->string('abnormal_flag')->nullable(); // L (low), H (high), LL (very low), HH (very high)
            $table->text('interpretation')->nullable(); // Interpretação do resultado
            $table->text('comments')->nullable();
            $table->json('attachments')->nullable(); // Caminhos para imagens/anexos
            $table->datetime('result_datetime')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('request_item_id')->references('id')->on('lab_request_items')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('lab_tests')->onDelete('restrict');
            
            $table->index('request_item_id');
            $table->index(['test_id', 'is_abnormal']);
        });

        // Tabela de equipamentos de laboratório
        Schema::create('lab_equipment', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_tag')->unique()->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive', 'broken'])->default('active');
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('calibration_date')->nullable();
            $table->date('next_calibration_due')->nullable();
            $table->text('specifications')->nullable();
            $table->text('maintenance_notes')->nullable();
            $table->boolean('requires_calibration')->default(false);
            $table->integer('calibration_interval_days')->default(365);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            
            $table->index(['clinic_id', 'status']);
        });

        // Tabela de controle de qualidade
        Schema::create('lab_quality_controls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clinic_id');
            $table->uuid('test_id');
            $table->uuid('equipment_id')->nullable();
            $table->string('control_name');
            $table->string('control_level'); // Low, Normal, High
            $table->string('lot_number');
            $table->date('expiry_date');
            $table->decimal('target_value', 10, 2);
            $table->decimal('sd', 10, 2); // Desvio padrão
            $table->decimal('measured_value', 10, 2);
            $table->boolean('is_acceptable')->default(true);
            $table->text('comments')->nullable();
            $table->datetime('run_datetime');
            $table->uuid('technician_id');
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('lab_tests')->onDelete('cascade');
            $table->foreign('equipment_id')->references('id')->on('lab_equipment')->onDelete('set null');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('restrict');
            
            $table->index(['clinic_id', 'test_id']);
            $table->index(['control_level', 'is_acceptable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_quality_controls');
        Schema::dropIfExists('lab_equipment');
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('lab_request_items');
        Schema::dropIfExists('lab_requests');
        Schema::dropIfExists('lab_profile_tests');
        Schema::dropIfExists('lab_test_profiles');
        Schema::dropIfExists('lab_tests');
        Schema::dropIfExists('lab_test_categories');
    }
};
