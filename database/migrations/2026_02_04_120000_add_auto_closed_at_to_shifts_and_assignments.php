<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vardiya bitiş saatinden 30 dk sonra kurye kapatmazsa sistem otomatik kapatır.
 * Bu alanlar otomatik kapatılan kayıtları işaretlemek için kullanılır.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->timestamp('auto_closed_at')->nullable()->after('admin_notes');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->timestamp('auto_closed_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('auto_closed_at');
        });

        Schema::table('shift_assignments', function (Blueprint $table) {
            $table->dropColumn('auto_closed_at');
        });
    }
};
