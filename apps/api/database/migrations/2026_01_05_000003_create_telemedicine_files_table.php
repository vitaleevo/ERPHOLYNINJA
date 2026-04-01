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
        Schema::create('telemedicine_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('telemedicine_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // mime type
            $table->integer('file_size'); // em bytes
            $table->enum('file_category', ['document', 'exam', 'prescription', 'other'])->default('other');
            $table->timestamps();

            $table->index(['session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemedicine_files');
    }
};
