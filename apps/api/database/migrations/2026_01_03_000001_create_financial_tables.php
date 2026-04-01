<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->string('category')->nullable();
            $table->string('supplier')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('accounts_receivable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'received', 'overdue', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'multicaixa', 'insurance'])->nullable();
            $table->string('reference')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['draft', 'issued', 'paid', 'cancelled'])->default('draft');
            $table->date('issue_date');
            $table->date('due_date');
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('consultation_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 12, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->date('reference_month');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('accounts_receivable');
        Schema::dropIfExists('accounts_payable');
    }
};
