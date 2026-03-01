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
        Schema::table('shift_assignments', function (Blueprint $table) {
            // Kuryenin fiilen başladığı saat (null ise vardiya başlangıcı kabul edilir)
            $table->time('actual_start_time')->nullable()->after('status');
            
            // Kuryenin fiilen bitirdiği saat (null ise vardiya bitişi kabul edilir)
            $table->time('actual_end_time')->nullable()->after('actual_start_time');
            
            // Erken bitirme nedeni (kaza, hastalık, vb.)
            $table->string('end_reason')->nullable()->after('actual_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropColumn(['actual_start_time', 'actual_end_time', 'end_reason']);
        });
    }
};
