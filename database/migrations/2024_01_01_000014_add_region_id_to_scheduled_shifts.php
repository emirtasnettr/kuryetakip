<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Planlı vardiyalara region_id ekle
 * 
 * Artık vardiyalar bölge (region) bazlı olacak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_shifts', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_shifts', function (Blueprint $table) {
            $table->dropColumn('region_id');
        });
    }
};
