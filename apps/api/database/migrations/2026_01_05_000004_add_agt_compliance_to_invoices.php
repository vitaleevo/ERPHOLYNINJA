<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Campos para conformidade AGT
            $table->string('vat_number')->nullable()->after('invoice_number');
            $table->decimal('vat_rate', 5, 2)->default(14.00)->after('total_amount');
            $table->decimal('vat_amount', 15, 2)->default(0)->after('vat_rate');
            $table->decimal('subtotal', 15, 2)->after('vat_amount');
            $table->decimal('withholding_tax', 15, 2)->default(0)->comment('Retenção na fonte')->after('subtotal');
            
            // Tipo de fatura conforme AGT
            $table->enum('invoice_type', ['factura', 'fatura-recibo', 'nota_credito', 'nota_debito'])->default('factura')->after('status');
            
            // Sistema de numeração fiscal
            $table->string('at_code')->nullable()->comment('Código da AGT')->after('invoice_number');
            $table->string('at_hash')->nullable()->comment('Hash da AGT')->after('at_code');
            $table->datetime('at_datetime')->nullable()->after('at_hash');
            
            // Estado da fatura para e-Factura
            $table->enum('agt_status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending')->after('status');
            $table->text('agt_response')->nullable()->after('agt_status');
            
            // Série e número sequencial
            $table->string('series', 10)->default('2026')->after('invoice_number');
            $table->integer('sequential_number')->nullable()->after('series');
            
            $table->index(['series', 'sequential_number']);
            $table->index('agt_status');
        });

        // Tabela para comunicação com AGT
        Schema::create('agt_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->string('request_id')->unique();
            $table->text('request_payload');
            $table->text('response_payload')->nullable();
            $table->integer('response_status')->nullable();
            $table->enum('status', ['pending', 'success', 'error'])->default('pending');
            $table->timestamps();
            
            $table->index('request_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agt_communications');
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['series', 'sequential_number']);
            $table->dropIndex('agt_status');
            
            $table->dropColumn([
                'vat_number',
                'vat_rate',
                'vat_amount',
                'subtotal',
                'withholding_tax',
                'invoice_type',
                'at_code',
                'at_hash',
                'at_datetime',
                'agt_status',
                'agt_response',
                'series',
                'sequential_number'
            ]);
        });
    }
};
