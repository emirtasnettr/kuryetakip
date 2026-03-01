<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->default(18)->after('package_rate')->comment('KDV oranı (%) - tutarlar KDV dahil kabul edilir, hesaplamada KDV hariç gösterilir');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_settings', function (Blueprint $table) {
            $table->dropColumn('vat_rate');
        });
    }
};
