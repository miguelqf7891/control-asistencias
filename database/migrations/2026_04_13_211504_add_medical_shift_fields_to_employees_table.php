<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('shift_type', 50)->default('full_8_20')->after('card_no');
            $table->time('custom_start_time')->nullable()->after('shift_type');
            $table->time('custom_end_time')->nullable()->after('custom_start_time');
            $table->integer('break_minutes')->default(60)->after('custom_end_time');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['shift_type', 'custom_start_time', 'custom_end_time', 'break_minutes']);
        });
    }
};
