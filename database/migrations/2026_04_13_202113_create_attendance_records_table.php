<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->datetime('check_time'); // Fecha/Hora de marcación
            $table->integer('location_id')->default(103); // Location ID
            $table->string('verify_code', 20)->nullable(); // VerifyCode (FACE/FP)
            $table->enum('type', ['entry', 'exit'])->nullable(); // Tipo de marcación
            $table->time('scheduled_entry')->nullable(); // Hora teórica de entrada
            $table->time('scheduled_exit')->nullable();  // Hora teórica de salida
            $table->enum('status', ['on_time', 'late', 'early_exit', 'overtime'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para reportes
            $table->index('check_time');
            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
