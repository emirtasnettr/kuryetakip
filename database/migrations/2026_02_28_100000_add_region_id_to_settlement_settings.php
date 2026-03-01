<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('id')->constrained('regions')->onDelete('cascade');
        });
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->unique('region_id');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropUnique(['region_id']);
        });
    }
};
