<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de medicamentos (cadastro)
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('dosage')->nullable(); // 500mg, 10ml, etc.
            $table->string('form')->nullable(); // comprimido, xarope, injetável
            $table->string('route')->nullable(); // oral, intravenosa, etc.
            $table->string('composition')->text()->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('registration_number')->nullable(); // registro ANVISA/INFARMED
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('requires_special_control')->default(false);
            $table->text('indications')->nullable();
            $table->text('contraindications')->nullable();
            $table->text('side_effects')->nullable();
            $table->decimal('reference_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['clinic_id', 'name']);
            $table->index(['clinic_id', 'generic_name']);
        });

        // Tabela de lotes de medicamentos
        Schema::create('medication_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('batch_number');
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date');
            $table->integer('initial_quantity');
            $table->integer('current_quantity');
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2);
            $table->string('storage_location')->nullable();
            $table->enum('status', ['active', 'expired', 'recalled', 'depleted'])->default('active');
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->index(['clinic_id', 'medication_id']);
            $table->index(['expiry_date', 'status']);
        });

        // Tabela de controle de estoque (movimentações)
        Schema::create('pharmacy_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('medication_batches')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('set null');
            $table->enum('type', ['entry', 'exit', 'adjustment', 'transfer', 'loss']);
            $table->integer('quantity');
            $table->integer('balance_after');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference_type')->nullable(); // Sale, Purchase, Adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->index(['clinic_id', 'medication_id']);
            $table->index(['type', 'created_at']);
        });

        // Tabela de vendas/dispensação
        Schema::create('pharmacy_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('prescription_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('set null'); // vendedor
            $table->string('invoice_number')->unique()->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'multicaixa', 'insurance', 'credit'])->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('completed');
            $table->text('observations')->nullable();
            $table->timestamps();
            
            $table->index(['clinic_id', 'created_at']);
            $table->index(['patient_id', 'status']);
        });

        // Itens da venda
        Schema::create('pharmacy_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_batch_id')->constrained('medication_batches')->onDelete('restrict');
            $table->string('medication_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('dosage')->nullable();
            $table->text('instructions')->nullable(); // como usar
            $table->timestamps();
        });

        // Alertas e notificações
        Schema::create('pharmacy_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('medication_batch_id')->nullable()->constrained('medication_batches')->onDelete('cascade');
            $table->enum('type', ['low_stock', 'expiry_soon', 'expired', 'out_of_stock']);
            $table->string('title');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->timestamps();
            
            $table->index(['clinic_id', 'type']);
            $table->index(['read', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_alerts');
        Schema::dropIfExists('pharmacy_sale_items');
        Schema::dropIfExists('pharmacy_sales');
        Schema::dropIfExists('pharmacy_stocks');
        Schema::dropIfExists('medication_batches');
        Schema::dropIfExists('medications');
    }
};
