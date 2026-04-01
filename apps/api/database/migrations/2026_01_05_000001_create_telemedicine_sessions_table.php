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
        Schema::create('telemedicine_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->unique(); // ID único da sessão de vídeo
            $table->string('meeting_url')->nullable(); // URL da reunião
            $table->string('moderator_password')->nullable(); // Senha do médico
            $table->string('attendee_password')->nullable(); // Senha do paciente
            $table->enum('status', ['scheduled', 'started', 'ended', 'cancelled'])->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->text('recording_url')->nullable(); // URL da gravação (se houver)
            $table->json('settings')->nullable(); // Configurações da sessão
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->index(['session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemedicine_sessions');
    }
};
