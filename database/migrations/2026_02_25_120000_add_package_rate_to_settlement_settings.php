<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->decimal('package_rate', 10, 2)->default(0)->after('photo_compliance_bonus')->comment('Paket başı ücret (TL)');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->dropColumn('package_rate');
        });
    }
};
