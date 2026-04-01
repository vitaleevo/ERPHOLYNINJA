<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('clinic_id')->nullable()->after('id');
            $table->string('role')->default('receptionist')->after('password');
            $table->unsignedBigInteger('specialty_id')->nullable()->after('role');
            $table->string('phone')->nullable()->after('specialty_id');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('specialty_id')->references('id')->on('specialties')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropColumn(['clinic_id', 'role', 'specialty_id', 'phone', 'avatar', 'is_active']);
        });
    }
};