<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 20)->unique(); // ID Number
            $table->string('name', 255); // Nombre completo
            $table->string('department', 100)->default('OUR COMPANY');
            $table->string('card_no', 50)->nullable(); // CardNo
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('employee_number');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
